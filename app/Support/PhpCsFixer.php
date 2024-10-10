<?php

namespace App\Support;

use App\Actions\ElaborateSummary;
use App\Contracts\Tool;
use App\Project;
use ArrayIterator;
use PhpCsFixer\ConfigInterface;
use PhpCsFixer\ConfigurationException\InvalidConfigurationException;
use PhpCsFixer\Console\ConfigurationResolver;
use PhpCsFixer\Error\ErrorsManager;
use PhpCsFixer\Finder;
use PhpCsFixer\Runner\Runner;
use PhpCsFixer\ToolInfo;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class PhpCsFixer extends Tool
{
    public static function getFinder(): Finder
    {
        return Finder::create()
            ->notName([
                '*.blade.php',
            ])
            ->ignoreDotFiles(true)
            ->ignoreVCS(true);
    }

    public function lint(): int
    {
        $this->heading('Linting using PHP CS Fixer');

        return $this->process();
    }

    public function fix(): int
    {
        $this->heading('Fixing using PHP CS Fixer');

        return $this->process();
    }

    private function process(): int
    {
        $output = app()->get(OutputInterface::class);

        $configurationResolver = new ConfigurationResolver(
            $this->getConfig(),
            [
                'config' => $this->getConfigFilePath(),
                'allow-risky' => 'yes',
                'diff' => $output->isVerbose(),
                'dry-run' => $this->reviveConfig->get('mode') === 'lint',
                'path' => $this->reviveConfig->get('paths'),
                'path-mode' => ConfigurationResolver::PATH_MODE_OVERRIDE,
                'stop-on-violation' => false,
                'verbosity' => $output->getVerbosity(),
                'show-progress' => 'true',
            ],
            Project::path(),
            new ToolInfo,
        );

        $changes = (new Runner(
            $this->getConfig()->getFinder(),
            $configurationResolver->getFixers(),
            $configurationResolver->getDiffer(),
            app()->get(EventDispatcher::class),
            app()->get(ErrorsManager::class),
            $configurationResolver->getLinter(),
            $configurationResolver->isDryRun(),
            $configurationResolver->getCacheManager(),
            $configurationResolver->getDirectory(),
            $configurationResolver->shouldStopOnViolation()
        ))->fix();

        $totalFiles = count(new ArrayIterator(iterator_to_array(
            $configurationResolver->getFinder(),
        )));

        return app()->get(ElaborateSummary::class)->execute($totalFiles, $changes);
    }

    private function getConfig(): ConfigInterface
    {
        $config = $this->includeConfig();

        return $config->setFinder($this->updateFinder($config->getFinder()));
    }

    /**
     * Update the finder with the paths and exclude from the config.
     * We are bypassing resolveFinder() in ConfigurationResolver
     * to allow for us to use the global revive config.
     */
    private function updateFinder(Finder $finder): Finder
    {
        collect($this->reviveConfig->get('paths', []))->each(function ($path) use ($finder) {
            if (is_dir($path)) {
                $finder = $finder->in($path);
            } elseif (is_file($path)) {
                $finder = $finder->append([$path]);
            }
        });

        collect($this->reviveConfig->get('exclude', []))->each(function ($path) use ($finder) {
            if (is_dir($path)) {
                $finder = $finder->exclude($path);
            } elseif (is_file($path)) {
                $finder = $finder->notPath($path);
            }
        });

        return $finder;
    }

    private function includeConfig(): ConfigInterface
    {
        $config = include $this->getConfigFilePath();

        if (! $config instanceof ConfigInterface) {
            throw new InvalidConfigurationException("The PHP CS Fixer config file does not return a 'PhpCsFixer\ConfigInterface' instance.");
        }

        return $config;
    }

    private function getConfigFilePath(): string
    {
        return (string) collect([
            Project::path() . '/.php-cs-fixer.dist.php',
            Project::path() . '/.php-cs-fixer.php',
            base_path('standards/.php-cs-fixer.dist.php'),
        ])->first(fn ($path) => file_exists($path));
    }
}
