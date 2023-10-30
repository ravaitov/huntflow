<?php

namespace App;

use App\Exceptions\AppException;

class CheckWebHookApp extends AbstractApp
{
    protected string $webHookUrl = 'https://app.zemser.ru/huntflow/applicant';
    protected string $webHookEvent = 'APPLICANT';

    public function __construct()
    {
        $this->config = Config::instance();
        $this->appName ??= 'Проверка вебхука';
        $this->endPoint = sprintf('accounts/%s/hooks', $this->config->conf('account_id'));
        parent::__construct();
    }

    protected function protectRun(): void
    {
        $response = $this->httpClient()->get($this->endPoint);
        $this->apiResult = json_decode($response->getBody());
        $this->status = $response->getStatusCode();

        if ($this->status !== 200)
            throw new AppException("Error status=$this->status\n" . print_r($this->apiResult, 1));
    }

    protected function finish(): void
    {
        foreach ($this->apiResult->items as $item) {
            if ($item->url === $this->webHookUrl && in_array($this->webHookEvent, $item->webhook_events)) {
                if ($item->active)
                    return;
                (new DeleteWebHookApp())->run(['id' => $item->id]);
            }
        }
        (new CreateWebHookApp())->run();
    }

}