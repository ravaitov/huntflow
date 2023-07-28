<?php

namespace App;

class UpdateDivisionsBatch
{
    public function run(): void
    {
        (new GetDivisionsApp)->run();
        (new UpdateDivisionsApp)->run();
        sleep(1);
        (new MappingDivisions())->run();
    }
}