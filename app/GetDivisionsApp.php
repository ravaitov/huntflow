<?php

namespace App;

use App\Exceptions\AppException;
use App\Rest\RestWH;

class GetDivisionsApp extends AbstractApp
{
    private array $divisionsRow = [];
    private RestWH $rest;

    private array $requests = [
        'lists.element.get' => [
            'params' => [
                'IBLOCK_TYPE_ID' => 'lists',
                'IBLOCK_ID' => '115',
            ],
            'result' => null,
        ],
        'lists.section.get' => [
            'params' => [
                'IBLOCK_TYPE_ID' => 'lists',
                'IBLOCK_ID' => '115',
                'SELECT' => ['ID', 'NAME', 'IBLOCK_ID'],
            ],
            'result' => null,
        ],
    ];

    public readonly string $key;

    public function __construct()
    {
        $this->key = 'GetDivisionsApp';
        $this->appName = 'Получение подразделений';
        parent::__construct(Config::APP_ID);
        $this->rest = new RestWH();
        $this->timeout = 20;
        $this->tryCount = 6;
    }

    protected function protectRun(): void
    {
        $result = $this->getFromBitrix();
        $result = $this->rangeDivisions($result);
        $result = $this->recurs($result); //print_r($result);

        $this->setResult(['items' => $result]);
        $this->logger->log('Получены Подразделения из B24');
    }

    private function getFromBitrix(): array
    {
        foreach ($this->requests as $method => &$item) {
            if (is_array($item['result']))
                continue;

            $item['result'] = $this->rest->getBig($method, $item['params']);
            if (!$this->rest->error())
                continue;

            $item['result'] = null;
            $this->status = $this->rest->error()['status'];
            throw new AppException("REST Error: status=$this->status\n" . print_r($this->rest->error(), 1));
        }

        $result = [];
        foreach ($this->requests as $i) {
            $result = array_merge($i['result'], $result);
        }

        return $result;
    }

    private function rangeDivisions(array $rowData): array
    {
        $res = [];
        foreach ($rowData as $item) {
            $res[$item['ID']] = $item;
        }

        foreach ($res as $item) {
            if (empty($item['IBLOCK_SECTION_ID']))
                continue;
            $res[$item['IBLOCK_SECTION_ID']]['items'][] = $item;
        }

        return array_filter($res, fn ($x) => empty($x['IBLOCK_SECTION_ID']));
    }

    private function recurs(array $a): array
    {
        $res = [];
        foreach ($a as $item) {
            $i = ['name' => $item['NAME'], 'foreign' => $item['ID'], 'meta' => null];
            if (array_key_exists('items', $item)) {
                $i['items'] = $this->recurs($item['items']);
            }
            $res[] = $i;
        }

        return $res;
    }
}