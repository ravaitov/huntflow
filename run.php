<?php
require_once __DIR__ . '/vendor/autoload.php';


function terminateErrorLog(string $log): void
{
    echo $log;
    file_put_contents(
        __DIR__ . '/log/run_error.txt',
        sprintf("%s %s\n", date('Y-m-d H:i:s'), $log),
        FILE_APPEND
    );
    exit("\n");
}

if (empty($argv[1])) {
    terminateErrorLog("Need argument!\t$argv[0] {UpdateDivisionsBatch|TokenRefreshApp|...}");
}

try {
    $app = eval("return new App\\$argv[1]();");
} catch (Throwable $t) {
    terminateErrorLog($t->getMessage());
}

if (!$app) {
    terminateErrorLog("Error: incorrect arg $argv[1]");
}

$app->run();
