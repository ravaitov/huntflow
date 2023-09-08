<?php

namespace App;

use App\Exceptions\AppException;

class DeleteWebHookApp extends CheckWebHookApp
{
    public function __construct()
    {
        $this->appName = 'Удаление вебхука';
        parent::__construct();
    }

    protected function finish(): void  {}

    protected function protectRun(): void
    {
        $response = $this->httpClient()->delete($this->endPoint . '/' . ($this->params['id'] ?? 0));
        $this->status = $response->getStatusCode();

        if ($this->status > 299)
            throw new AppException("Error status=$this->status");
    }
}