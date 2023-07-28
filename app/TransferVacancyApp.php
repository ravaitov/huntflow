<?php

namespace App;

use App\DataBase\DataBase;
use App\Exceptions\AppException;
use App\Utils\ForeignMapper;

class TransferVacancyApp extends AbstractApp
{
    private ForeignMapper $divisionMapper;
    private ForeignMapper $vacancyMapper;

    private array $first;
    private array $second;

    public function __construct()
    {
        $this->appName = 'Передача вакансии';
        $dataBase = new DataBase('database');
        $this->divisionMapper = new ForeignMapper($dataBase, 'division_map');
        $this->vacancyMapper = new ForeignMapper($dataBase, 'vacancy_map');
        parent::__construct(Config::APP_ID);
    }

    public function prepare(array $params): void
    {
        $this->first = $params['first'];
        $this->second = $params['second'];

        if (empty($params['first']['division_id']))
            throw new AppException('!!division_id error', true);

        if (empty($mappedDivisionId = $this->divisionMapper->foreignToId($params['first']['division_id'])))
            throw new AppException('!!division ID mapper error', true);

        $this->first['account_division'] = $mappedDivisionId;


//        $this->logger->log(print_r($params, 1));
    }

    protected function protectRun(): void
    {
        $json = json_encode($this->presentRequest(), JSON_UNESCAPED_UNICODE);
        $url = sprintf('accounts/%s/vacancies', $this->config->conf('account_id'));

        $response = $this->httpClient()->post($url, ['body' => $json]);
        $this->apiResult = json_decode($response->getBody());
        $this->status = $response->getStatusCode();

        if ($this->status !== 200)
            throw new AppException("Error status=$this->status\n" . print_r($this->apiResult, 1));
    }

    protected function finish(): void
    {
        $this->vacancyMapper->createMap($this->apiResult->id, $this->first['id']);
        $this->logger->log('Успешно');
    }

    private function presentRequest(): array
    {
        $result = $this->first;
        unset($result['id']);
        unset($result['division_id']);

        foreach ($this->second as $section => $ar) {
            $result[$section] = $this->presentSection($ar);
        }

        $result['fill_quotas'] = [];
//        $this->logger->log(print_r($result, 1)); //exit();

        return $result;
    }

    private function presentSection(array $fields): string
    {
        $result = '<ul>';
        foreach ($fields as $array) {
            $item = current($array);
            $result .= sprintf('<li>%s: %s</li>', key($item), current($item));
        }

        return $result . '</ul>';
    }
}