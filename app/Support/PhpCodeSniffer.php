<?php

namespace App\Support;

use App\Project;
use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Runner;
use Symfony\Component\Console\Output\OutputInterface;

class PhpCodeSniffer extends Tool
{
    public function lint(): int
    {
        $this->heading('Linting using PHP_CodeSniffer');

        return $this->process('runPHPCS', $this->getPaths());
    }

    public function fix(): int
    {
        $this->heading('Fixing using PHP_CodeSniffer');

        $fix = $this->process('runPHPCBF', $this->getPaths());

        $lint = $this->process('runPHPCS', ['-n', '--report=summary', ...$this->getPaths()]);

        if ($lint !== 0) {
            $this->failure('PHP Code_Sniffer found errors that cannot be fixed automatically.');
        }

        return $fix || $lint ? 1 : 0;
    }

    /**
     * @param  array<int, string>  $params
     */
    private function process(string $tool, array $params = []): int
    {
        $serverArgv = $_SERVER['argv'];

        $this->installDevanoxCodingStandard();

        $ignore = $this->reviveConfig->get('exclude')
            ? ['--ignore=' . implode(',', $this->reviveConfig->get('exclude'))]
            : [];

        $_SERVER['argv'] = [
            'Revive',
            '--standard=' . $this->getConfigFile(),
            ...$ignore,
            ...$params,
        ];

        $this->resetConfig($tool);

        $runner = new Runner;

        ob_start();

        $exitCode = $runner->$tool();

        app()->get(OutputInterface::class)->write(ob_get_contents());

        ob_end_clean();

        $_SERVER['argv'] = $serverArgv;

        return $exitCode;
    }

    /**
     * @return array<int, string>
     */
    private function getPaths(): array
    {
        return $this->reviveConfig->get('paths') === [Project::path()]
            ? $this->getDefaultDirectories() : $this->reviveConfig->get('paths');
    }

    private function installDevanoxCodingStandard(): void
    {
        Config::setConfigData('installed_paths', base_path('standards/Devanox'), true);
    }

    /**
     * Config uses a private static property $overriddenDefaults
     * which doesn't allow us to update the config between runs
     * we need to reset it so we can also lint in the fix command.
     */
    private function resetConfig(string $tool): void
    {
        if (defined('PHP_CODESNIFFER_CBF') === false) {
            define('PHP_CODESNIFFER_CBF', $tool === 'runPHPCBF');
        }

        // @phpstan-ignore-next-line
        invade(new Config)->overriddenDefaults = [];
    }

    private function getConfigFile(): string
    {
        return match (true) {
            file_exists(Project::path() . '/.phpcs.xml') => Project::path() . '/.phpcs.xml',
            file_exists(Project::path() . '/phpcs.xml') => Project::path() . '/phpcs.xml',
            file_exists(Project::path() . '/.phpcs.xml.dist') => Project::path() . '/.phpcs.xml.dist',
            file_exists(Project::path() . '/phpcs.xml.dist') => Project::path() . '/phpcs.xml.dist',
            file_exists(Project::path() . '/vendor/mrchetan/php_standard/ruleset.xml') => Project::path() . '/vendor/mrchetan/php_standard/ruleset.xml', // @codingStandardsIgnoreLine
            default => 'Devanox',
        };
    }

    /**
     * @return array<int, string>
     */
    private function getDefaultDirectories(): array
    {
        return array_filter(
            [
                Project::path() . '/app',
                Project::path() . '/config',
                Project::path() . '/database',
                Project::path() . '/public',
                Project::path() . '/resources',
                Project::path() . '/routes',
                Project::path() . '/tests',
                ...$this->reviveConfig->get('include', []),
            ],
            fn ($dir) => is_dir($dir)
        ) ?: [Project::path()];
    }
}
