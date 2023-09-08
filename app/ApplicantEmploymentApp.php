<?php

namespace App;

use App\Exceptions\AppException;
use App\Utils\ForeignMapper;
use App\Rest\RestWH;

class ApplicantEmploymentApp extends AbstractApp
{
    private ForeignMapper $vacancyMapper;
    private ForeignMapper $divisionMapper;
    private RestWH $restWH;
    private int $docId = 0;

    public function __construct(int $appId = Config::APP_ID)
    {
        $this->appName = 'Перенос кандидата в Б24';
        parent::__construct($appId);
        $this->vacancyMapper = new ForeignMapper($this->config->dataBase(), 'vacancy_map');
        $this->divisionMapper = new ForeignMapper($this->config->dataBase(), 'division_map');
        $this->restWH = new RestWH();
    }

    public function prepare(array $params = []): void
    {
        $json = $params['json'] ?? '{}';
        $params = json_decode($json, true);
        $type = $params['event']['applicant_log']['type'];
        if ($type !== 'STATUS') {
            $this->logger->log("type = $type != STATUS");
            exit();
        }
        if ($this->config->conf('applicant_status_ok') !== ($params['event']['applicant_log']['status']['id'] ?? 0)) {
            $this->logger->log($params['event']['applicant_log']['status']['name'] ?? 'status name ??');
            exit();
        }
        $this->docId = $this->vacancyMapper->idToForeign($params['event']['applicant_log']['hired_in_fill_quota']['vacancy_request'] ?? 0);
        $this->setResult([
            'first_name' => $params['event']['applicant']['first_name'] ?? '',
            'last_name' => $params['event']['applicant']['last_name'] ?? '',
            'middle_name' => $params['event']['applicant']['middle_name'] ?? '',
            'phone' => $params['event']['applicant']['phone'] ?? '',
            'email' => $params['event']['applicant']['email'] ?? '',
            'position' => $params['event']['applicant_log']['vacancy']['position'] ?? '',
            'account_division_id' => $this->divisionMapper->idToForeign($params['event']['applicant_log']['vacancy']['account_division']['id'] ?? 0),
            'account_division_name' => $params['event']['applicant_log']['vacancy']['account_division']['name'] ?? '',
//            'photo' => $params['event']['applicant']['photo']['url'] ?? '',
            'files' => $params['event']['applicant_log']['files'][0]['url'] ?? '',
            'employment_date' => $params['event']['applicant_log']['employment_date'],
            'created' => $params['event']['applicant_log']['created'],
            'author' => $params['meta']['author']['name'] ?? '',
            'author_email' => $params['meta']['author']['email'] ?? '',
        ]);
        $this->logger->log(print_r($params, 1));
        $this->logger->log('json=' . json_encode($params, JSON_UNESCAPED_UNICODE));
        if (!$this->docId) {
            $this->logger->log('Нет ID элемента! (1)', Config::ERROR);
            exit();
        }
    }

    protected function protectRun(): void
    {
        $this->logger->log("Start BP docId=$this->docId");
        $res = $this->restWH->call(
            'bizproc.workflow.start',
            [
                'TEMPLATE_ID' => '1698',
                'DOCUMENT_ID' => ['lists', 'Bitrix\\Lists\\BizprocDocumentLists', $this->docId],
                'PARAMETERS' => [
                    'json' => json_encode($this->getResult(), JSON_UNESCAPED_UNICODE),
                ],
            ]
        );
        if ($this->restWH->error()) {
            throw new AppException(print_r($this->restWH->error(), 1));
        }
    }
}