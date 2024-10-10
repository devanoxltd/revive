<?php

namespace App\Providers;

use App\Actions\ElaborateSummary;
use App\Actions\FixCode;
use App\Commands\DefaultCommand;
use App\Contracts\PathsRepository;
use App\Contracts\PintInputInterface;
use App\Output\ProgressOutput;
use App\Output\SummaryOutput;
use App\Project;
use App\Repositories\ConfigurationJsonRepository;
use App\Repositories\GitPathsRepository;
use App\Repositories\PintConfigurationJsonRepository;
use App\Support\ReviveConfig;
use Illuminate\Support\ServiceProvider;
use PhpCsFixer\Error\ErrorsManager;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class PintServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ErrorsManager::class, fn () => new ErrorsManager);

        $this->app->singleton(EventDispatcher::class, fn () => new EventDispatcher);

        $this->app->singleton(PintInputInterface::class, function () {
            $input = $this->app->get(InputInterface::class);

            return new ArrayInput(
                ['--test' => $input->getArgument('command') === 'lint', 'path' => Project::paths($input)],
                resolve(DefaultCommand::class)->getDefinition()
            );
        });

        $this->app->singleton(FixCode::class, fn () => new FixCode(
            resolve(ErrorsManager::class),
            resolve(EventDispatcher::class),
            resolve(PintInputInterface::class),
            resolve(OutputInterface::class),
            new ProgressOutput(
                resolve(EventDispatcher::class),
                resolve(PintInputInterface::class),
                resolve(OutputInterface::class),
            )
        ));

        $this->app->singleton(ElaborateSummary::class, fn () => new ElaborateSummary(
            resolve(ErrorsManager::class),
            resolve(PintInputInterface::class),
            resolve(OutputInterface::class),
            new SummaryOutput(
                resolve(ConfigurationJsonRepository::class),
                resolve(ErrorsManager::class),
                resolve(PintInputInterface::class),
                resolve(OutputInterface::class),
            )
        ));

        $this->app->singleton(ConfigurationJsonRepository::class, function () {
            $config = (string) collect([
                Project::path() . '/pint.json',
                base_path('standards/pint.json'),
            ])->first(fn ($path) => file_exists($path));

            $reviveConfig = ReviveConfig::scopeConfigPaths(ReviveConfig::loadLocal());

            return new PintConfigurationJsonRepository($config, null, $reviveConfig['exclude']);
        });

        $this->app->singleton(PathsRepository::class, fn () => new GitPathsRepository(
            Project::path(),
        ));
    }
}
