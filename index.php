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
