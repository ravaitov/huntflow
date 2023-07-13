<?php

namespace App;

class TokenRefreshApp extends AbstractApp
{
    public function __construct()
    {
        $this->appName = 'Обновление токенов';
        parent::__construct(Config::APP_ID);
        $this->timeout = 20;
    }

    protected function protectRun(): void
    {
        $response = $this->httpClient()->post(
            'token/refresh', ['json' => ['refresh_token' => $this->config->conf('refresh_token')]]
        );
        $result = json_decode($response->getBody());
        $this->status = $response->getStatusCode();
        if ($this->status === 200) {
            $this->logger->log('Ok 200');
            $this->saveTokens($result);
        } else {
            $this->logger->log(
                sprintf('Status = %s; title = %s', $response->getStatusCode(), $result->errors[0]->title)
            );
        }
    }

    private function saveTokens(mixed $result): void
    {
        if (isset($result->access_token) && isset($result->refresh_token)) {
            $tokens = $result->access_token . "\n" . $result->refresh_token;
            file_put_contents($this->config->conf('tokens'), $tokens);
            file_put_contents($this->config->conf('tokens') . '.bak', $tokens);
            $this->logger->log('Токены сохранены');
            $this->config->loadTokens();
        } else {
            $this->logger->log('!!!Токены не сохранены!!! Необходимо вручную восстановить токены');
        }
    }
}

/*
        $response = $this->httpClient()->request('GET', 'me');

        echo $response->getStatusCode(), PHP_EOL;
        print_r($result);
        echo $result->errors[0]->title,PHP_EOL;

 */