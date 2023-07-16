<?php

namespace App\Support;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

class UserScript extends Tool
{
    /**
     * @param  array<int, string>  $command
     */
    public function __construct(
        protected string $name,
        protected array $command,
        protected ReviveConfig $reviveConfig,
    ) {
    }

    public function lint(): int
    {
        $this->heading('Linting using ' . $this->name);

        return $this->process();
    }

    public function fix(): int
    {
        $this->heading('Fixing using ' . $this->name);

        return $this->process();
    }

    private function process(): int
    {
        $reviveConfig = ReviveConfig::loadLocal();

        $process = new Process($this->command);
        $process->setTimeout($reviveConfig['processTimeout'] ?? 60);
        $output = app()->get(OutputInterface::class);

        try {
            // @codingStandardsIgnoreLine
            $process->run(fn ($type, $buffer) => $output->write($buffer));

            return $process->getExitCode();
        } catch (ProcessTimedOutException $e) {
            $error = '<br />You can overwrite this timeout with the processTimeout key in your revive.json file.';
            $this->failure($e->getMessage() . $error);

            return 1;
        }
    }
}
