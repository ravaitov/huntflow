<?php

namespace App;

class TransferVacancyReqApp extends TransferVacancyApp
{
    public function __construct()
    {
        $this->appName = 'Передача заявки на вакансию';
        parent::__construct();
    }

    public function prepare(array $params = []): void
    {
        parent::prepare($params);
        $this->first['account_vacancy_request'] = $this->config->conf('account_vacancy_request');
    }

    protected function url(): string
    {
        return sprintf('accounts/%s/vacancy_requests', $this->config->conf('account_id'));
    }
}