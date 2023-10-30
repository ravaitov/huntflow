<?php

namespace App;

use App\Exceptions\AppException;

class VacancyListForSite extends AbstractApp
{
    private array $stages = [
        'getVacancyIdList',
        'getRequestIdList',
        'getVacRequests'
    ];
    private array $vacId = [];
    private array $reqVacId;

    public function __construct()
    {
        $this->appName = 'Список вакансий для сайта';
        parent::__construct(Config::APP_ID);
        $this->timeout = 30;
        $this->tryCount = 5;
        $this->pause = 30;
        reset($this->stages);
    }

    protected function protectRun(): void
    {
        $this->logger->log('Шаг= ' . current($this->stages));
        $this->{current($this->stages)}();
    }

    private function getAndCheck(string $param): void
    {
        $response = $this->httpClient()->get($this->endPoint . $param);
        $this->apiResult = json_decode($response->getBody(), true);
        $this->status = $response->getStatusCode();

        if ($this->status !== 200)
            throw new AppException("Error status=$this->status\n" . print_r($this->apiResult, 1));
    }

    private function getVacancyIdList(): void
    {
        $this->getAndCheck('vacancies');
        foreach ($this->apiResult['items'] as $item) {
            if ($item['state'] === 'OPEN') {
                $this->vacId[] = $item['id'];
            }
        }
        $this->logger->log(print_r($this->vacId, 1));
    }

    private function getRequestIdList(): void
    {
        $this->reqVacId = [];
        foreach ($this->vacId as $vacancy) {
            $this->getAndCheck('vacancy_requests?vacancy_id=' . $vacancy);
            foreach ($this->apiResult['items'] as $item) {
                $this->reqVacId[] = $item['id'];
            }
        }
        $this->logger->log(print_r($this->reqVacId, 1));
    }

    private function getVacRequests(): void
    {

    }

    protected function finish(): void
    {
        if (next($this->stages) === false) {
            return;
        }
        $this->tryCount = 5;
        $this->run();
    }
}