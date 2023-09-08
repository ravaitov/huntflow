<?php

namespace App;

use App\Exceptions\AppException;

class CreateWebHookApp extends CheckWebHookApp
{
    public function __construct()
    {
        $this->appName = 'Создание вебхука';
        parent::__construct();
    }

    protected function finish(): void  {}

    protected function protectRun(): void
    {
        $json = sprintf(
            '{"secret": "rvdrux", "url": "%s", "active": 1, "webhook_events":  [ "%s"]}',
            $this->webHookUrl,
            $this->webHookEvent
        );
        $response = $this->httpClient()->post($this->endPoint, ['body' => $json]);
        $this->apiResult = json_decode($response->getBody(), true);
        $this->status = $response->getStatusCode();

        if ($this->status !== 200)
            throw new AppException("Error status=$this->status\n" . print_r($this->apiResult, 1));
    }
}