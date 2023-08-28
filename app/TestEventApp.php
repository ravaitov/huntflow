<?php

namespace App;

class TestEventApp extends AbstractApp
{
    public function __construct(int $appId = 0)
    {
        $this->appName = 'События Test';
        parent::__construct($appId);
    }

    public function prepare(array $params): void
    {
        $this->logger->log(print_r($params, 1));
    }
}