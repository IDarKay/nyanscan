<?php
require __DIR__ . '/../private/utils/functions.php';

if (strtoupper($_SERVER["REQUEST_METHOD"]) !== "GET") {
    http_response_code(405);
    exit();
}

// get uri setting
$token = $_GET["t"] ?? null; // token
$user = $_GET["user"] ?? null; // user_id
$deny = $_GET["deny"] ?? '0'; // bool need deny request

// invalid uri
if ($token === null || $user === null) {
    http_response_code(400);
    exit();
}

// get verification statement
$raw = getDB()->select(TABLE_VERIFICATION, ['id', 'type', 'value'], ['user_id' => $user, "token" => $token], 1);
if ($raw) {
    // delete verification
	getDB()->delete(TABLE_VERIFICATION, ['user_id' => $user, "token" => $token]);
	switch (intval($raw['type'])) {
		case VERIFICATION_TYPE_EMAIL_CREATE:

			$status = getDB()->select(TABLE_USER, ['status'], ['id' => $user], 1);
			if ($status) {
				getDB()->update(TABLE_USER, ['status' => intval($status["status"]) | STATUS_EMAIL_VERIFIED], ['id' => $user]);
				header('Location: /auth/verification-success');
				exit();
			}
			break;
        case VERIFICATION_TYPE_PASSWORD_FORGET:
		case VERIFICATION_TYPE_PASSWORD_CHANGE:
			if ($deny !== '1' && $raw['value']) {
				$token = createMD5Token();
				getDB()->update(TABLE_USER, ['password' => $raw['value'], "token" => $token], ['id' => $user]);
			}
			header('Location: /auth/verification-success');
			exit();
			break;
		case VERIFICATION_TYPE_EMAIL_CHANGE:
			if ($deny !== '1') {
				$token = createMD5Token();
				getDB()->update(TABLE_USER, ['email' => $raw['value'], "token" => $token], ['id' => $user]);
			}
			header('Location: /auth/verification-success');
			exit();
			break;
        case VERIFICATION_TYPE_DELETE:
            if ($deny !== '1') {
                $token = createMD5Token();
                $status = getDB()->select(TABLE_USER, ['status'], ['id' => $user], 1);
                if ($status) {
                    getDB()->update(TABLE_USER, ['email' => createMD5Token(), 'username' => 'utilisateur_supprime', "token" => $token, 'status' => intval($status["status"]) | STATUS_DELETE], ['id' => $user]);
                }
            }
            header('Location: /');
            exit();
            break;
	}
}
header('Location: /auth/verification-failed');