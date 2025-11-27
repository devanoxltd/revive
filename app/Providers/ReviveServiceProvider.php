<?php

namespace App\Providers;

use App\Project;
use App\Support\ReviveConfig;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Console\Input\InputInterface;

class ReviveServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ReviveConfig::class, function () {
            $input = $this->app->get(InputInterface::class);

            $mode = match ($input->getArgument('command')) {
                'lint' => 'lint',
                'fix' => 'fix',
                default => 'other',
            };

            $reviveConfig = ReviveConfig::loadLocal();

            return new ReviveConfig([
                'paths' => Project::paths($input),
                'using' => $input->getOption('using'),
                'mode' => $mode,
                'include' => $reviveConfig['include'] ?? [],
                'exclude' => $reviveConfig['exclude'] ?? [],
                'scripts' => $reviveConfig['scripts'] ?? [],
            ]);
        });
    }
}
