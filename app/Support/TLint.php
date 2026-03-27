<?php

namespace App\Support;

use App\Contracts\Tool;
use Illuminate\Console\Command;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;
use Tighten\TLint\Commands\BaseCommand;
use Tighten\TLint\Commands\FormatCommand;
use Tighten\TLint\Commands\LintCommand;

class TLint extends Tool
{
    public function lint(): int
    {
        $this->heading('Linting using TLint');

        return $this->process('lint');
    }

    public function fix(): int
    {
        $this->heading('Fixing using TLint');

        return $this->process('format');
    }

    private function process(string $command): int
    {
        $tlintCommand = $command === 'lint' ? new LintCommand : new FormatCommand;
        $success = $this->executeCommand($tlintCommand);

        if ($success && $tlintCommand instanceof FormatCommand) {
            $this->success('Checking for any remaining issues after fixing...');
            if (! $this->executeCommand(new LintCommand)) {
                $this->failure('Some issues could not be fixed automatically.');

                return Command::FAILURE;
            }
        }

        return $success ? Command::SUCCESS : Command::FAILURE;
    }

    private function executeCommand(BaseCommand $tlintCommand): bool
    {
        $tlintCommand->config->excluded = [
            ...$tlintCommand->config->excluded ??
        [],
            ...$this->reviveConfig->get('exclude', []),
        ];

        $application = new Application;
        $application->add($tlintCommand);
        $application->setAutoExit(false);

        $errors = collect($this->reviveConfig->get('paths'))
            ->map(fn ($path) => $this->executeCommandOnPath($path, $application))
            ->filter();

        if ($errors->isEmpty()) {
            $this->success('No issues found.');

            return true;
        }

        return false;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws Throwable
     * @throws NotFoundExceptionInterface
     */
    private function executeCommandOnPath(string $path, Application $application): int
    {
        $path = '"' . str_replace('\\', '\\\\', $path) . '"';

        $command = $application->has('lint') ? 'lint' : 'format';

        $output = new BufferedOutput;

        $exitCode = $application->run(
            new StringInput("{$command} {$path}"),
            $output
        );

        if ($exitCode !== 0) {
            app()->make(OutputInterface::class)->write($output->fetch());
        }

        return $exitCode;
    }
}
