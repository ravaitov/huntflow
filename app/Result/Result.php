<?php

namespace App\Result;

use App\Traits\SingletonTrait;

class Result
{
    use SingletonTrait;

    private array $results;

    public function getResult(string $key): ?array
    {
        return $this->results[$key] ?? null;
    }

    public function setResult(string $key, array $result): void
    {
        $this->results[$key] = $result;
    }
}