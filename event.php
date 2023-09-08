<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Logger\Logger;

Logger::instance()->echoLog = false;

$headers = array_change_key_case(getallheaders(), CASE_LOWER);

$event = explode('/', $_SERVER['REQUEST_URI'])[2] ?? '';
$class = [
        'applicant' => 'ApplicantEmploymentApp',
        'vacancy' => 'TestEventApp',
        'offer' => 'TestEventApp',
        'vacancy_request' => 'TestEventApp',
    ] [$event] ?? 'TestEventApp';

try {
    Logger::instance()->log("--- Event=$event -> $class REMOTE=" . $_SERVER['REMOTE_ADDR']);
    $app = eval("return new App\\$class();");
} catch (Throwable $t) {
    Logger::instance()->log("!!!Fatal\n" . $t->getMessage());
    exit();
}

$json = file_get_contents("php://input");

try {
    $app->prepare(['json' => $json]);
    $app->run();
} catch (Throwable $t) {
    Logger::instance()->log("!!! Error: " . $t->getMessage() . "\njson=$json\n");
    http_response_code(200); //!! иначе вебхук удалится
}

/*
print_r($_SERVER);
print_r(file_get_contents("php://input"));
exit ('top2');
*/