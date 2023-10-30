<?php

namespace App;

use App\Utils\ForeignMapperWithName;

class MappingDivisions extends AbstractApp
{
    private ForeignMapperWithName $mapper;

    public function __construct()
    {
        $this->appName = 'Mapping divisions';
        parent::__construct(Config::APP_ID);
        $this->mapper = new ForeignMapperWithName($this->config->dataBase(), 'division_map');
    }

    protected function protectRun(): void
    {
        $response = $this->httpClient()->get(
            'accounts/' . $this->config->conf('account_id') . '/divisions'
        );
        $this->apiResult = json_decode($response->getBody());
        $this->status = $response->getStatusCode();
        if ($this->status === 200)
            return;
        $this->logger->log(sprintf('Status = %s; title = %s', $response->getStatusCode(), $this->apiResult->errors[0]->title));
    }

    protected function finish(): void
    {
        $this->logger->log('Ok 200... DB proc...');
        foreach ($this->apiResult->items as $item) {
            try {
                $this->mapper->createMapWithName($item->id, $item->foreign, $item->name);
            } catch (\Throwable $t) {
                $this->logger->log($t->getMessage() . "; id=$item->id", Config::ERROR);
            }
        }
        $this->logger->log('Добавлено/изменено ' . $this->mapper->count . ' строк');
    }
}