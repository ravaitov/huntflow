<?php

namespace App;

use App\Exceptions\AppException;
use App\Result\Result;

class UpdateDivisionsApp extends AbstractApp
{
    private bool $delayedTask = false;
    private string $taskId = '';

    public function __construct()
    {
        $this->appName = 'Подразделения -> Хантфлоу';
        parent::__construct(Config::APP_ID);
        $this->timeout = 30;
    }

    protected function protectRun(): void
    {
        if ($this->delayedTask)
            $this->delayedProcStatus();
        else
            $this->mainProc();
    }

    protected function finish(): void
    {
        if (!$this->delayedTask && $this->taskId) {
            $this->delayedTask = true;
            $this->tryCount = 3;
            $this->logger->log("Ожидание статуса задачи task_id=" . $this->taskId);
            sleep(5);
            $this->status = 400;
        }
    }

    private function mainProc(): void
    {
        if (!($divisions = Result::instance()->getResult('GetDivisionsApp')))
            throw new AppException("Нет данных по подразделениям", true);

        $json = json_encode($divisions, JSON_UNESCAPED_UNICODE);
        $url = sprintf('accounts/%s/divisions/batch', $this->config->conf('account_id'));

        $response = $this->httpClient()->post($url, ['body' => $json]);
        $result = json_decode($response->getBody());
        $this->status = $response->getStatusCode();

        if ($this->status !== 200)
            throw new AppException("Error status=$this->status\n" . print_r($result, 1));

        $this->taskId = $result->payload->task_id ?? '';
        $this->logger->log("Успешно запущен процесс task_id=" . $this->taskId);
    }

    private function delayedProcStatus(): void
    {
        $response = $this->httpClient()->get(
            sprintf('accounts/%s/delayed_tasks/%s', $this->config->conf('account_id'), $this->taskId)
        );
        $result = json_decode($response->getBody());
        $this->status = $response->getStatusCode();
        if ($this->status !== 200)
            throw new AppException("Error status=$this->status\n" . print_r($result, 1));

        $state = $result->state;
        if ($state !== 'success')
            throw new AppException("Не завершено state=$state");

        $this->logger->log("Успешно завершен процесс task_id=" . $this->taskId);
    }
}

/*
        $response = $this->httpClient()->request('GET', 'me');
        $response = $this->httpClient()->get('accounts/176006/divisions');

        echo $response->getStatusCode(), PHP_EOL;
        print_r($result);

 */