<?php

namespace App;

use App\Exceptions\AppException;
use App\Traits\SingletonTrait;
use App\Exceptions\ConfigException;

class Config
{
    use SingletonTrait;

    /**
     * from table zsmicroapp.applog_levels
     */
    const ERROR = 1;
    const WARNING = 2;
    const IMPORTANT = 3;
    const EVENT = 4;
    const DEBUG = 5;

    const LOG_DB = [self::ERROR, self::WARNING, self::IMPORTANT, self::EVENT]; // levels logged into DB

    const APP_ID = -1;

    public array $level_names = [
        0 => '',
        self::ERROR => 'Ошибка: ',
        self::WARNING => 'Предупреждение: ',
        self::IMPORTANT => 'Важно! ',
        self::EVENT => '',
        self::DEBUG => 'Отладка: ',
    ];

    private array $conf = [
        'version' => '0.1.0',
        'comment' => '',
        'base_uri' => 'https://api.huntflow.ru/v2/', //api
        'access_token' => '',
        'refresh_token' => '',
        'tokens' => 'tokens.txt',
        'log_file' => '??', // auto init
        'log_limit' => 90, // log files count limit
        'pending_errors' => '??', // auto init
        'app_id' => self::APP_ID, // !!!
        'account_id' => 176006, //"КОНТАБИЛИТА"
        'db_log' => [],
    ];

    public function conf(string $key): array|string|int
    {
        if (!isset($this->conf[$key])) {
            throw new ConfigException("Config error! Unknown key='$key'");
        }

        return $this->conf[$key];
    }


    public function setParam(string $key, $param): void
    {
        $this->conf[$key] = $param;
    }

    public function appName(): string
    {
        return $this->conf['app_names'][$this->conf['app_id'] ?? 0] ?? '';
    }

    protected function init(): void
    {
        date_default_timezone_set('Europe/Moscow');
        try {
            $this->conf['log_dir'] = realpath(__DIR__ . '/../log/') . DIRECTORY_SEPARATOR;
            $this->conf['log_file'] = $this->conf['log_dir'] . 'log_%s.txt';
//            $this->conf['pending_errors'] = $this->conf['log_dir'] . 'pending_errors';
            $this->conf['stor_dir'] = realpath(__DIR__ . '/../storage/') . DIRECTORY_SEPARATOR;
            $this->conf['tokens'] = $this->conf['stor_dir'] . $this->conf['tokens'];
            $this->loadTokens();
        } catch (\Throwable $t) {
            throw new ConfigException("Config init error! " . $t->getMessage());
        }
//        print_r($this->conf);
    }

    public function loadTokens(): void
    {
        [$this->conf['access_token'], $this->conf['refresh_token']] =
            array_map(fn($i) => trim($i), file($this->conf['tokens']));
    }
}