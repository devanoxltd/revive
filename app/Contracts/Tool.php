<?php

namespace App\Contracts;

use App\Concerns\CommandHelpers;
use App\Support\ReviveConfig;

abstract class Tool
{
    use CommandHelpers;

    public function __construct(
        protected ReviveConfig $reviveConfig,
    ) {}

    abstract public function lint(): int;

    abstract public function fix(): int;
}
