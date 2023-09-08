<?php

namespace App;

use App\Exceptions\AppException;
use App\Rest\RestWH;

class TestEventApp extends AbstractApp
{
    public function __construct(int $appId = Config::APP_ID)
    {
        $this->appName = 'События Test';
        parent::__construct($appId);
    }

    public function prepare(array $params = []): void
    {
        $this->logger->log(print_r($params, 1));
    }

    protected function protectRun(): void
    {
        $restWH = new RestWH();
        $res = $restWH->call(
            'bizproc.workflow.start',
            [
                'TEMPLATE_ID' => '1698',
                'DOCUMENT_ID' => ['lists', 'Bitrix\\Lists\\BizprocDocumentLists', '1839689'],
                'PARAMETERS' => [
                    'json' => '{"name": "Тестов2"}'
                ],
            ]
        );
            if ($restWH->error()) {
            throw new AppException(print_r($restWH->error(), 1));
        }
    }
}