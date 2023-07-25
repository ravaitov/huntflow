<?php

namespace App;

class ImportVacancyApp extends AbstractApp
{
    public function __construct()
    {
        $this->appName = 'Импорт вакансии';
        parent::__construct(Config::APP_ID);
    }

}