<?php

namespace App;

use App\DataBase\DataBase;
use App\Logger\Logger;
use App\Result\Result;
use GuzzleHttp\Client;
use stdClass;

class AbstractApp
{
    protected Config $config;
    protected DataBase $base;
    protected Logger $logger;
    protected $tryCount = 3;
    protected $pause = 3;
    protected $timeout = 10;
    protected string $appName;
    protected Result $result;
    protected int $status = 400;
    protected array|stdClass $apiResult;
    protected array $params;

    public readonly string $key;

    public function __construct(int $appId = Config::APP_ID)
    {
        $this->key ??= 'common';
        $this->config = Config::instance();
        $this->result = Result::instance();
        $this->config->setParam('app_id', $appId);
        $this->appName ??= $this->config->appName();
        $this->config->setParam('app_name', $this->appName);
        $this->logger = Logger::instance();
        $this->logger->log(">>> Старт: " . $this->appName . '. V=' . $this->config->conf('version'), Config::EVENT);
    }

    public function __destruct()
    {
//        $this->sendPendingErrors();
        $this->logger->log('<<< Завершение: ' . $this->appName . "\n", Config::EVENT);
    }

    public function prepare(array $params = []): void {} //вызов только в контроллере, event!!!

    public function run(array $params = []): void
    {
        $this->params = $params;
        while ($this->tryCount-- > 0) {
            try {
                $this->protectRun();
                $this->status = 200;
                $this->tryCount = 0;
                http_response_code($this->status);
                $this->finish();
            } catch (Exceptions\AppException $exception) {
                $this->logger->log($exception->getMessage(), Config::ERROR);
                $this->tryCount = $exception->terminate ? 0 : $this->tryCount;
                $this->status = 400;
            } catch (\Throwable $exception) {
                $this->logger->log($exception->getMessage(), Config::ERROR);
                $this->status = 400;
            } finally {
                $this->checkTokenRefresh();
                if ($this->tryCount > 0) {
                    sleep($this->pause); // + rand(0, 5));
                } else {
                    http_response_code($this->status);
                }
            }
        }
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getResult(): ?array
    {
        return $this->result->getResult($this->key);
    }

    public function setResult(?array $result): void
    {
        $this->result->setResult($this->key, $result);
    }

    protected function httpClient(): Client
    {
        return new Client([
            'headers' => [
                'Authorization' => 'Bearer ' . $this->config->conf('access_token'),
            ],
            'base_uri' => $this->config->conf('base_uri'),
            'timeout' => $this->timeout,
            'http_errors' => false,
            'verify' => false
        ]);
    }

//    protected function onError ()

    protected function protectRun(): void    {}

    protected function finish(): void    {}

    protected function checkFileOrDie(string $filePath, int $minutes = 0): void
    {
        if (!file_exists($filePath)) {
            $this->logger->log(sprintf('Не создан файл %s ', $filePath), Config::ERROR);
            exit("!!! NO $filePath\n");
        }

        if (!$minutes) {
            return;
        }

        $fTime = filemtime($filePath);
        if ($fTime === false) {
            $this->logger->log(sprintf('Ошибка доступа к файлу %s', $filePath), Config::ERROR);
            exit("!!! NO access $filePath\n");
        }
        if ($minutes && (time() - $fTime) > 60 * $minutes) {
            $this->logger->log(sprintf('Устаревший файл %s (более %d мин.)', $filePath, $minutes), Config::ERROR);
            exit("!!! Old $filePath\n");
        }
    }

    private function checkTokenRefresh():void
    {
        if ('token_expired' === ($this->apiResult->errors[0]->detail ?? '')) {
            (new TokenRefreshApp())->run();
            $this->tryCount++;
        }
    }
}