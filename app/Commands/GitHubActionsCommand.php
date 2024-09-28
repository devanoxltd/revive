<?php

namespace App\Commands;

use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;

use function Termwind\render;

class GitHubActionsCommand extends Command
{
    protected $signature = 'github-actions';

    protected $description = 'Publish GitHub Actions';

    public function handle(): int
    {
        $choices = [
            'Lint only' => 'revive-lint',
            'Fix and commit' => 'revive-fix',
            'Fix, commit, and update .git-blame-ignore-revs' => 'revive-fix-blame',
        ];

        $branch = $this->anticipate('What is the name of your primary branch?', ['main', 'develop', 'master'], 'main');
        $choice = $this->choice('Which GitHub action would you like?', array_keys($choices), 0);

        $workflowName = $choices[$choice];

        if (Str::contains($workflowName, 'fix')) {
            $this->warn('The resulting commit will stop any currently running workflows and will not trigger another.');
            $this->warn('Checkout Revive\'s documentation for a workaround.');
            if (! $this->confirm('Do you wish to continue?', true)) {
                return Command::FAILURE;
            }
        }

        $workflow = file_get_contents(__DIR__ . "/../../stubs/github-actions/{$workflowName}.yml");
        $workflow = str_replace('YOUR_BRANCH_NAME', $branch, $workflow);

        if (! is_dir(getcwd() . '/.github/workflows')) {
            mkdir(getcwd() . '/.github/workflows', 0777, true);
        }

        file_put_contents(getcwd() . "/.github/workflows/{$workflowName}.yml", $workflow);

        $this->success('GitHub Actions added');

        return Command::SUCCESS;
    }

    private function success(string $message): void
    {
        render(<<<HTML
            <div class="py-1 ml-2">
                <div class="px-1 bg-green-300 text-black">Success</div>
                <em class="ml-1">
                {$message}
                </em>
            </div>
        HTML);
    }
}
