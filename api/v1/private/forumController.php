<?php
/**
 *
 * @param $method string
 * @param $function array
 * @param $query array
 * @api POST forum/category
 * @api POST forum/topic
 * @api POST forum/message
 * @api POST forum/reply
 *
 * @api GET forum/category/root
 * @api GET forum/category/all
 * @api GET forum/category/{cat-id}
 * @api GET forum/topic/{topic-id}
 * @api GET forum/category/{cat-id}/topics?limit=[0-20]&offset=[0+]
 * @api GET forum/message/{msg-id}
 * @api GET forum/messages
 *
 *
 * invoker
 */
function invokeForm(string $method, array $function, array $query)
{
    if ($method === "POST") {
        if (count($function) === 1 && $function[0] === "category") _create_category();
        elseif (count($function) === 1 && $function[0] === "topic") _create_topic();
        elseif (count($function) === 1 && $function[0] === "message") _create_message();
        elseif (count($function) === 1 && $function[0] === "reply") _create_reply();
    } elseif ($method === "GET") {
        if (count($function) === 2 && $function[0] == "category") {
            if ($function[1] === "all") _get_all_category();
            if ($function[1] === "root") _get_root_category();
            else _get_category($function[1]);
        } elseif (count($function) === 3 && $function[0] == "category" && $function[2] === "topics") {
            _get_topic_from_category($function[1], $query);
        } elseif (count($function) === 2 && $function[0] === "topic") {
            _get_topic($function[1]);
        } elseif (count($function) === 2 && $function[0] === "message") {
            _get_message($function[1], $query);
        } elseif (count($function) === 1 && $function[0] === "messages") {
            _get_messages($query);
        }
    } else bad_method();
}


// GET

/**
 * return data for root forum view
 * @return void => success => array
 * @version 1.0.0
 * @author Alice.B
 * @get void
 * @api GET forum/category/root
 */
function _get_root_category()
{
    $user = get_log_user();
    $perm = $user->get_permission_level();
    $raw = get_all_full_category($perm);
    $final = [];
    foreach ($raw as $row) {
        $cat = $row["cat_id"];
        if (!isset($final[$cat]))
            $final[$cat] = ["id" => $cat, "name" => $row["cat_name"], "description" => $row["cat_description"], "permission_view" => $row["permission_view"], "permission_create" => $row["permission_create"], "topics" => []];
        $topic = [
            "id" => $row["topic_id"],
            "name" => $row["topic_name"],
            "date_inserted" => $row["topic_date_inserted"],
            "last_message" => [
                "id" => $row["message_id"],
                "date_inserted" => $row["message_date_inserted"],
                "author" => [
                    "id" => $row["author_id"],
                    "username" => $row["author_username"]
                ]
            ]
        ];
        $final[$cat]["topics"][] = $topic;
    }
    success($final);
}

/**
 * @api GET forum/category/all
 * return all visible category for user
 * @version 1.0.0
 * @author Alice.B
 */
function _get_all_category()
{
    success(get_all_visible_category(get_log_user()->get_permission_level()));
}

/**
 * @api GET forum/category/{cat-id}
 * return one category from id
 * @version 1.0.0
 * @author Alice.B
 */
function _get_category($id)
{
    if (empty($id)) bad_request('invalid category');
    $cat = get_category($id);
    $perm = get_log_user()->get_permission_level();
    if (!$cat || $cat["permission_view"] > $perm) bad_request('invalid category');
    success($cat);
}

/**
 * @api GET forum/topic/{topic-id}
 * return one topic from id
 * @version 1.0.0
 * @author Alice.B
 */
function _get_topic($id)
{
    if (empty($id)) bad_request('invalid category');
    $req = getDB()->get_pdo()->prepare("SELECT T.id AS id, T.name AS name, date_inserted, last_message, category, C.name AS category_name, C.description AS category_description, permission_create, permission_view FROM PAE_FORUM_TOPIC AS T LEFT JOIN PAE_FORUM_CATEGORY C on T.category = C.id WHERE T.id=:topic LIMIT 1;");
    $req->execute(['topic' => $id]);
    $data = $req->fetch(PDO::FETCH_ASSOC);
    if ($data['permission_view'] > get_log_user()->get_permission_level()) bad_request();
    success($data);
}

/**
 * @api GET forum/category/{cat-id}/topics?limit=[0-20]&offset=[0+]
 * return all topics from category
 * @version 1.0.0
 * @author Alice.B
 */
function _get_topic_from_category($id, $query)
{
    $limit = max(0, min(30, intval($query['limit'] ?? 10)));
    $offset = max(0, intval($query['offset'] ?? 0));
    $count = $query["count"] ?? '0';

    $user = get_log_user();
    $perm = $user->get_permission_level();

    if (empty($id)) bad_request('invalid category');


    $category = get_category($id);
    if (!$category || $category['permission_view'] > $perm) bad_request('invalid category');

    $data = [];

    $data["topics"] = array_map(function ($array) {
        return concatenate_array_by_prefix($array, ["message", "author"]);
    }, getDB()->select_set_settings('SELECT
                   T.id            AS id,
                   T.name          AS name,
                   T.date_inserted AS date_inserted,
                   M.id            AS message_id,
                   M.date_inserted AS message_date_inserted,
                   A.id            AS author_id,
                   A.username      AS author_username

            FROM PAE_FORUM_TOPIC AS T
                     LEFT JOIN PAE_FORUM_MESSAGE AS M ON T.last_message = M.id
                     LEFT JOIN PAE_USER A ON A.id = M.author', ["T.category" => $id], $limit, 'T.last_message DESC', $offset));
    $data["category"] = [
        "id" => $id,
        "name" => $category["name"],
        "description" => $category["description"],
    ];
    if ($count !== '0') {
        if ($data["topics"]) {
            $data["total"] = getDB()->count(TABLE_FORUM_TOPIC, 'id', ['category' => $id]);
        } else $data["total"] = 0;
    }
    success($data);
}

/**
 * @api GET forum/message/{msg-id}
 * return one message
 * @version 1.0.0
 * @author Alice.B
 */
function _get_message($id, $query)
{
    if (empty($id)) bad_request('invalid message');

    $data = getDB()->select(VIEW_FULL_FORUM_MESSAGE, ['*'], ["id" => $id], 1);
    if ($data['category_permission_view'] > get_log_user()->get_permission_level()) bad_request('invalid message');
    else success(concatenate_array_by_prefix($data, ["replay", "author", "topic"]));
}

/**
 * @api GET forum/message/{msg-id}
 * return one message
 * @version 1.0.0
 * @author Alice.B
 */
function _get_messages($query)
{
    $user = get_log_user();
    $perm = $user->get_permission_level();
    $limit = max(1, min(50, intval($query['limit'] ?? 10)));
    $offset = max(0, intval($query['offset'] ?? 0));
    $topic = $query["topic"] ?? null;
    $author = $query["author"] ?? null;

    if ($topic === null && $author === null) bad_request("no topic or author specified");

    $where = [];
    if ($author !== null) $where['author_id'] = $author;
    if ($topic !== null) $where['topic_id'] = $topic;
    $where['category_permission_view'] = ['v' => $perm, 'o' => '<='];

    $data = getDB()->select(VIEW_FULL_FORUM_MESSAGE, ['*'], $where, $limit, 'date_inserted DESC', $offset);

    $elements = [];
    foreach ($data as $d) {
        $elements[] = concatenate_array_by_prefix($d, ["replay", "author", "topic"]);
    }

    success([
        "count" => count($elements),
        "elements" => $elements
    ]);
}

// POST

/**
 * Create new message
 * permission depends on category permission  @return void
 * @api POST forum/topic
 * @version 1.0.0
 * @author Alice.B
 * @see _create_category, must be connected
 *
 * @post topic string not null =>  id of topic
 * @post message string not null => content message
 */
function _create_message()
{
    $user = get_log_user();
    if (!$user->is_connected()) unauthorized();

    $errors = [];

    $message = $_POST["message"] ?? "";
    $topic = $_POST["topic"] ?? null;

    if ($topic === null) bad_request('invalid topic');
    $req = getDB()->get_pdo()->prepare("SELECT T.id AS id, C.permission_create AS permission_create FROM PAE_FORUM_TOPIC AS T LEFT JOIN PAE_FORUM_CATEGORY C on T.category = C.id WHERE T.id=:topic LIMIT 1;");
    $req->execute(['topic' => $topic]);
    $sql_topic = $req->fetch(PDO::FETCH_ASSOC);

    if (!$sql_topic || $sql_topic['permission_create'] > $user->get_permission_level()) bad_request('invalid topic');

    if (strlen($message) < 1 || strlen($message) > 2000) $errors["$message"] = 'Le $message doit contenir au minimum 1 caractére et au maximum 2000 !';

    if (count($errors) !== 0) bad_request($errors);

    $data = ["author" => $user->getId(), "topic" => $sql_topic["id"], "content" => $message];

    getDB()->insert(TABLE_FORUM_MESSAGE, $data);
    update_topic_last_message($sql_topic["id"]);
    success();
}

/**
 * Create message reply
 * permission depends on category permission  @return void
 * @api POST forum/topic
 * @version 1.0.0
 * @author Alice.B
 * @see _create_category, must be connected
 *
 * @post message string not null =>  content
 * @post reply string not null => id of message to reply
 */
function _create_reply()
{
    $user = get_log_user();
    if (!$user->is_connected()) unauthorized();
    $errors = [];

    $message = $_POST["message"] ?? "";
    $reply = $_POST["reply"] ?? null;

    if ($reply === null) bad_request('invalid reply');
    $req = getDB()->get_pdo()->prepare("SELECT M.id AS id, C.permission_create AS permission_create FROM PAE_FORUM_MESSAGE AS M LEFT JOIN PAE_FORUM_TOPIC T ON M.id = T.last_message LEFT JOIN PAE_FORUM_CATEGORY C ON T.category = C.id WHERE M.id=:message LIMIT 1;");
    $req->execute(['message' => $reply]);
    $sql_reply = $req->fetch(PDO::FETCH_ASSOC);
    if (!$sql_reply || $sql_reply['permission_create'] > $user->get_permission_level()) bad_request('invalid reply');

    if (strlen($message) < 1 || strlen($message) > 2000) $errors["$message"] = 'Le $message doit contenir au minimum 1 caractére et au maximum 2000 !';
    if (count($errors) !== 0) bad_request($errors);

    $data = ["author" => $user->getId(), "reply" => $sql_reply["id"], "content" => $message];
    getDB()->insert(TABLE_FORUM_REPLY, $data);
    success();

}

/**
 * Create new topic
 * permission depends on category permission  @return void
 * @api POST forum/topic
 * @version 1.0.0
 * @author Alice.B
 * @see _create_category, must be connected
 *
 * @post category string not null =>  id of category
 * @post title string not null
 * @post message string not null => first message @see _create_message
 */
function _create_topic()
{
    $user = get_log_user();
    if (!$user->is_connected()) unauthorized();

    $errors = [];

    $category = $_POST["category"] ?? "";
    $title = trim($_POST["title"] ?? "");
    $message = $_POST["message"] ?? "";

    if (empty($category)) bad_request('invalid category');
    $cat = get_category($category);
    if (!$cat || $cat['permission_create'] > $user->get_permission_level()) bad_request('invalid category');

    if (strlen($title) < 5 || strlen($title) > 100) $errors["title"] = 'Le titre doit contenir au minimum 5 caractéres et au maximum 100 !';
    if (strlen($message) < 10 || strlen($message) > 2000) $errors["$message"] = 'Le $message doit contenir au minimum 10 caractéres et au maximum 2000 !';

    if (count($errors) !== 0) bad_request($errors);

    getDB()->insert(TABLE_FORUM_TOPIC, ["name" => $title, "category" => $cat["id"]]);
    $id = getDB()->select(TABLE_FORUM_TOPIC, ['id'], ["name" => $title], 1, 'date_inserted DESC')['id'];
    getDB()->insert(TABLE_FORUM_MESSAGE, ["author" => $user->getId(), "topic" => $id, "content" => $message]);
    update_topic_last_message($id);
    success();
}

/**
 * Create new category
 * permission need  @return void
 * @api POST forum/category
 * @version 1.0.0
 * @author Alice.B
 * @see PERMISSION_CREATE_CATEGORY
 *
 * @post title string not null
 * @post description string not null
 * @post view int @see PERMISSION_MASK
 * @post creat int @see PERMISSION_MASK
 */
function _create_category()
{
    $user = get_log_user();
    if ($user->get_permission_level() < PERMISSION_CREATE_CATEGORY) unauthorized();
    $errors = [];


    $id = $_POST["id"] ?? '';
    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $view = $_POST["view"] ?? PERMISSION_DISCONNECT;
    $create = $_POST["create"] ?? PERMISSION_DISCONNECT;

    if (strlen($title) < 5 || strlen($title) > 100) $errors["title"] = 'Le titre doit contenir au minimum 5 caractéres et au maximum 100 !';
    if (strlen($description) > 255) $errors["description"] = 'La description doit contenir au maximum 255 caractéres !';

    if (!is_numeric($view) || ($view & PERMISSION_MASK) > $user->get_permission_level()) $errors["view"] = 'La préférence de vue est invalide !';
    if (!is_numeric($create) || ($create & PERMISSION_MASK) > $user->get_permission_level() || ($create | PERMISSION_MASK) < ($view | PERMISSION_MASK)) $errors["create"] = 'La préférence de création est invalide !';

    if (count($errors) === 0) {
        $data = [
            "name" => $title,
            "description" => $description,
            "permission_view" => $view & PERMISSION_MASK,
            "permission_create" => $create & PERMISSION_MASK
        ];
        if (empty($id)) {
            getDB()->insert(TABLE_FORUM_CATEGORY, $data);
        } else {
            if (!getDB()->select(TABLE_FORUM_CATEGORY, ['id'], ['id' => $id], 1)) bad_request("invalid category");
            getDB()->update(TABLE_FORUM_CATEGORY, $data, ['id' => $id]);
        }

        success();
    } else bad_request($errors);
}

// like / dislike
function _like_message()
{
}

// DELETE

function _block_message()
{
}

function _delete_message()
{
}

function _delete_topic()
{
}

function _delete_category()
{
}

// EDIT
function _edit_category()
{
}

function _edit_topic()
{
}

// content ; reply ;
function _edit_message()
{
}