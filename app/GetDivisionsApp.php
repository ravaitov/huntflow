<?php

namespace App;

use App\Exceptions\AppException;
use App\Rest\RestWH;

class GetDivisionsApp extends AbstractApp
{
    public readonly string $key;

    public function __construct()
    {
        $this->key = 'GetDivisionsApp';
        $this->appName = 'Получение подразделений';
        parent::__construct(Config::APP_ID);
        $this->timeout = 20;
    }

    protected function protectRun(): void
    {
        $result = $this->getFromBitrix();
        $result = $this->rangeDivisions($result);
        $result = $this->recurs($result);

        $this->setResult(['items' => $result]);
        $this->logger->log('Получены Подразделения из B24');
    }

    private function getFromBitrix(): array
    {
        $wh = new RestWH();
        $list = $wh->getBig(
            'lists.element.get',
            [
                'IBLOCK_TYPE_ID' => 'lists',
                'IBLOCK_ID' => '115',
            ],
        );
        $this->restErrorCheck($wh);

        $this->tryCount = 3;
        $section = $wh->getBig(
            'lists.section.get',
            [
                'IBLOCK_TYPE_ID' => 'lists',
                'IBLOCK_ID' => '115',
                'SELECT' => ['ID', 'NAME', 'IBLOCK_ID'],
            ],
        );
        $this->restErrorCheck($wh);

        return array_merge($section, $list);
    }

    private function restErrorCheck(RestWH $wh): void
    {
        if ($wh->error()) {
            throw new AppException("REST Error\n" . print_r($wh->error(), 1));
        }
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