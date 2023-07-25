<?php
require_once __DIR__ . '/vendor/autoload.php';
$headers = array_change_key_case(getallheaders(), CASE_LOWER);

if (!isset($headers['x-token'])) {
    http_response_code(401);
    exit();
}

if (!isset($headers['x-method'])) {
    http_response_code(405);
    exit();
}

$controller = new App\Controllers\Controller($headers['x-token'], $headers['x-method'], file_get_contents("php://input"));
$controller->run();

exit();
print_r($_GET);
print_r($_POST);
print_r(getallheaders());
print_r(file_get_contents("php://input"));
//file_put_contents('post1', print_r($_GET, 1));
//file_put_contents('post1', print_r($_POST, 1), FILE_APPEND);
//file_put_contents('post1', print_r(getallheaders(), 1), FILE_APPEND);
//echo getallheaders()['Token'], PHP_EOL;
//    $controller = new App\Controllers\Controller();
