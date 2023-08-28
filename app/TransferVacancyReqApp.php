<?php

namespace App;

class TransferVacancyReqApp extends TransferVacancyApp
{
    const account_vacancy_request = 15254;

    public function __construct()
    {
        $this->appName = 'Передача заявки на вакансию';
        parent::__construct();
    }

    public function prepare(array $params = []): void
    {
        parent::prepare($params);
        $this->first['account_vacancy_request'] = self::account_vacancy_request;
    }

    protected function url(): string
    {
        return sprintf('accounts/%s/vacancy_requests', $this->config->conf('account_id'));
    }
}