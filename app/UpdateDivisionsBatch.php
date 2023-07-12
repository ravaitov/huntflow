<?php

namespace App;

use App\GetDivisionsApp;
use App\UpdateDivisionsApp;

class UpdateDivisionsBatch
{
    public function run(): void
    {
        (new GetDivisionsApp)->run();
        (new UpdateDivisionsApp)->run();
    }
}