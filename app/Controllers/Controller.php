<?php

namespace App\Controllers;

use App\AbstractApp;
use App\Config;
use App\Logger\Logger;
use App\Exceptions\AppException;
use Throwable;

class Controller
{
    private AbstractApp $app;

    private function logAndDie(string $mesaage = '!!!', int $code = 400):void
    {
        Logger::instance()->log("Controller error: $mesaage\n", Config::ERROR);
        http_response_code($code);
        exit();
    }

    public function __construct(string $token, string $method, string $json)
    {
        if (Config::instance()->conf('x-token') !== $token)
            $this->logAndDie('x-token', 401);

        try {
            $this->app = eval("return new App\\$method();");
        } catch (Throwable $t) {
            $this->logAndDie("Класс $method не найден", 405);
        }

        try {
            $this->app->prepare(json_decode($json, true));
        } catch (Throwable $t) {
            $this->logAndDie("$method ->prepare error " . $t->getMessage());
        }
    }

    public function run():void
    {
        $this->app->run();
    }
}