<?php

namespace App\Support;

use App\Project;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use JsonException;
use Symfony\Component\Finder\Finder;

class ReviveConfig
{
    /**
     * @var array<int, string>
     */
    public static array $defaultExclude = [
        '_ide_helper_actions.php',
        '_ide_helper_models.php',
        '_ide_helper.php',
        '.phpstorm.meta.php',
        'bootstrap/cache',
        'build',
        'node_modules',
        'storage',
        'tests/Pest.php',
        'vendor',
    ];

    /**
     * @var array<int, string>
     */
    public static array $phpSuffixes = [
        '.php',
        '.php.inc',
        '.php.stub',
        '.php.skeleton',
    ];

    /**
     * @param  array<string, array<int, string>|string>  $config
     */
    public function __construct(
        protected array $config = []
    ) {
        $this->config = static::scopeConfigPaths($this->config);
    }

    /**
     * @param  array<string, array<int, string>|string>  $config
     * @return array<string, array<int, string>|string>
     */
    public static function scopeConfigPaths(array $config): array
    {
        $config['include'] = static::expandWildcards($config['include'] ?? []);

        $filteredExcludes = collect($config['exclude'] ?? [])
            ->reject(fn ($path) => in_array($path, static::$defaultExclude))
            ->reject(fn ($path) => Str::contains($path, ['node_modules', 'vendor']))
            ->toArray();

        $config['exclude'] = array_merge(
            static::expandWildcards($filteredExcludes),
            static::$defaultExclude
        );

        return $config;
    }

    /**
     * @param  array<int, string>  $paths
     * @return array<int, string>
     */
    public static function expandWildcards(array $paths): array
    {
        return collect($paths)->flatMap(fn ($path) => static::globPath($path))->values()->toArray();
    }

    /**
     * @return array<int, string>
     */
    public static function globPath(string $path): array
    {
        try {
            // Finder uses forward slashes even on windows
            $path = Str::of($path)->replace('\\', '/')->__toString();

            // Remove trailing / or /* from path before passing to finder
            $path = Str::of($path)->replaceMatches('/(\/|\/\*)$/', '')->__toString();

            if (Str::of($path)->endsWith(self::$phpSuffixes)) {
                $endsWith = Str::of($path)->afterLast('/');
                $path = Str::of($path)->beforeLast('/');

                $files = (new Finder)
                    ->ignoreUnreadableDirs()
                    ->ignoreDotFiles(false)
                    ->files()
                    ->in($path);
            } else {
                $endsWith = self::$phpSuffixes;
                $files = (new Finder)
                    ->ignoreUnreadableDirs()
                    ->files()
                    ->in($path);
            }

            return collect($files)
                ->map(
                    fn ($file) => Str::of($file->getPathName())->endsWith($endsWith)
                        // Fixes weird windows path issue with mixed slashes
                        ? Str::of($file->getPathName())->replace('\\', '/')->__toString()
                        : null
                )
                ->filter()
                ->all();
        } catch (Exception) {
            return collect(glob($path, GLOB_NOCHECK))
                ->filter(fn ($path) => file_exists($path))
                ->all();
        }
    }

    /**
     * @return array<string, mixed>
     *
     * @throws JsonException
     */
    public static function loadLocal(): array
    {
        if (file_exists(Project::path() . '/revive.json')) {
            return tap(json_decode(
                file_get_contents(Project::path() . '/revive.json'),
                true,
                512,
                JSON_THROW_ON_ERROR
            ), function ($configuration) {
                if (! is_array($configuration)) {
                    abort(1, 'The configuration file revive.json is not valid JSON.');
                }
            });
        }

        if (file_exists(Project::path() . '/vendor/devanoxltd/laravel-revive/revive.json')) {
            return tap(json_decode(
                file_get_contents(Project::path() . '/vendor/devanoxltd/laravel-revive/revive.json'),
                true,
                512,
                JSON_THROW_ON_ERROR
            ), function ($configuration) {
                if (! is_array($configuration)) {
                    abort(1, 'The configuration file revive.json is not valid JSON.');
                }
            });
        }

        return [];
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->config, $key, $default);
    }
}
