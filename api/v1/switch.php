<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include __DIR__ . '/private/forumController.php';

require($_SERVER['DOCUMENT_ROOT'] . '/private/utils/forum.php');

function my_error_handler()
{
    $last_error = error_get_last();
    if ($last_error && $last_error['type'] == E_ERROR) {
        header("HTTP/1.1 500 Internal Server Error");
    }
}

header("Content-Type: application/json");

function bad_method()
{
    json_exit(405, "Method Not Allowed", "Only accept POST");
}

function bad_request($reason = "")
{
    json_exit(400, "Bad Request", $reason);
}

function success($answer = "ok") {
    http_response_code(200);
    echo json_encode(["code" => 200, "answer" => $answer]);
    exit();
}

register_shutdown_function('my_error_handler');

$uri = explode('/', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$controller = $uri[3] ?? null;
$function = array_slice($uri, 4);
parse_str($_SERVER['QUERY_STRING'], $query);
$method = strtoupper($_SERVER["REQUEST_METHOD"]);

switch ($controller) {
    case 'forum':
        invokeForm($method, $function, $query);
        break;
    default:
        break;

}

print_r($uri);
print_r($method);
print_r($function);
print_r($query);

//default 404 error
header("HTTP/1.1 404 Not Found");
exit();
