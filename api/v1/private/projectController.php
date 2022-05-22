<?php

function invokeProject($method, $function, $query)
{
    if ($method === "POST") {
        if ($function[0] === 'create') _new_project();
        if ($function[0] === 'validation') _change_status_project();
    } elseif ($method === "GET") {
        if ($function[0] === 'user' && count($function) === 2)
            _fetch_user_projects($function[1]);
        elseif ($function[0] === 'all') _admin_fetch_projects();
    } else bad_method();
}

function _new_project()
{
    $user = get_log_user();
    if (!$user->is_connected()) unauthorized();

    if (getDB()->count(TABLE_PROJECT, 'id', ['author' => $user->getId(), 'status' => PROJECT_STATUS_WAIT_VERIFICATION]) > 1) {
        bad_request("l'utilisateur à dèja un projet en attente de vérification");
    }

    $title = $_POST["title"] ?? null;
    $format = $_POST["format"] ?? null;
    $direction = $_POST["direction"] ?? null;
    $description = $_POST["description"] ?? null;

    $error = [];

    if ($title === null || strlen($title) < 1 || strlen($title) > 100) $error[] = "Titre invalide !";
    if ($format === null || ($format != '1' && $format != '2')) $error[] = "Format de publication inavlide !";
    if ($direction === null || ($direction != '1' && $direction != '2')) $error[] = "Sens de lecture inavlide !";
    if ($description === null || strlen($title) < 1 || strlen($title) > 2000) $error[] = "Description trop longue (2000) ou inexistante !";

    if (empty($error)) {
        $img = download_image_from_post("picture", [PICTURE_FORMAT_JPG, PICTURE_FORMAT_PNG, PICTURE_FORMAT_WEBP], 500_000);
        if (is_numeric($img)) {
            switch ($img) {
                case -1:
                    $error[] = "Pas de vignette";
                    break;
                case -2:
                    $error[] = "Vignette trop lourde max 500Ko";
                    break;
                default:
                    json_exit(500, "Uploading error", "unknow");
                    break;
            }
        } else {
            $img->resize(180, 240);
            $img->set_author($user->getId());
            $img->set_title("Image pour le projet " . substr($title, 0, 24) . '...');
            $img->add_logo();
            $img->save();
            getDB()->insert(TABLE_PROJECT, [
                "author" => $user->getId(),
                "picture" => $img->get_id(),
                "title" => $title,
                "description" => $description,
                "reading_direction" => $direction,
                "format" => $format,
                "status" => PROJECT_STATUS_WAIT_VERIFICATION
            ]);
        }
    }
    success();
}

function _fetch_user_projects($userId)
{
    $user = get_log_user();
    // self
    if ($userId === 'me' || ($user->is_connected() && $user->getId() === $userId)) {
        success(getDB()->select(TABLE_PROJECT,
            ["id", "author", "picture", "title", "description", "format", "status", "date_inserted"],
            ["author" => $user->getId()],
            0, "date_inserted DESC"));
    } else {
        success(getDB()->select(
            TABLE_PROJECT,
            ["id", "author", "picture", "title", "description", "format", "date_inserted"],
            ["author" => $userId, "status" => PROJECT_STATUS_PUBLISHED],
            0, "date_inserted DESC"));
    }
}

function _admin_fetch_projects()
{
    // todo check perm
    // todo offset and limit
    success(getDB()->select(TABLE_PROJECT,
        ["id", "author", "picture", "title", "description", "format", "status", "date_inserted"],
        [],
        0, "date_inserted DESC"));
}

function _change_status_project() {
    // todo permission
    $status = $_POST["status"]??null;
    $project = $_POST["project"]??null;
    $reason = $_POST["reason"]??null;
    if ($reason === null) $reason = "";

    if ($status === null || !is_numeric($status) && intval($status) < 0 || $status > PROJECT_STATUS_PUBLISHED) {
        bad_request("wrong status");
    }

    $project = getDB()->select(TABLE_PROJECT, ['id', 'author', 'title'], ["id" => $project], 1);
    if (!$project) bad_request("wrong project");
    if (strlen($reason) > 255) bad_request("La raison ne dois pas deepasser les 255 caractéres");

    getDB()->update(TABLE_PROJECT, ["status" => $status], ["id" => $project]);

    // send mail
    $text_status = "";
    switch ($status) {
        case PROJECT_STATUS_WAIT_VERIFICATION: $text_status = "Attente de vérification"; break;
        case PROJECT_STATUS_REJECT: $text_status = "Rejeté"; break;
        case PROJECT_STATUS_ACCEPTED_NO_CONTENT: $text_status = "Accepté en attente de contenue"; break;
        case PROJECT_STATUS_PUBLISHED: $text_status = "Publié"; break;
    }

    if ($project["user"]) {
        $user = getDB()->select(TABLE_USER, ['username', 'email'], ['id' => $project["author"]], 1);
        if ($user) send_project_status_change_mail($text_status, $project['title'], $user["email"], $user["username"]);
    }
    success();
}