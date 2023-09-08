<?php

namespace App\Result;

use App\Traits\SingletonTrait;

class Result
{
    use SingletonTrait;

    public array $m;

    public function getResult(string $key): ?array
    {
        return $this->m[$key] ?? null;
    }

    public function setResult(string $key, array $result): void
    {
        $this->m[$key] = $result;
    }
}