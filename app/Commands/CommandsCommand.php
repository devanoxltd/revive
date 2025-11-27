<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;

class CommandsCommand extends Command
{
    protected $signature = 'commands';

    protected $description = 'Learn about Revive commands';

    public function handle(): int
    {
        return $this->call('list');
    }
}
