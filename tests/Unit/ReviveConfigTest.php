<?php

use App\Support\ReviveConfig;

it('provides config values', function () {
    $reviveConfig = new ReviveConfig([
        'paths' => ['path1', 'path2'],
        'using' => ['tlint', 'phpcs', 'php-cs-fixer', 'pint'],
    ]);

    expect($reviveConfig->get('paths'))->toBe(['path1', 'path2']);
    expect($reviveConfig->get('using'))->toBe(['tlint', 'phpcs', 'php-cs-fixer', 'pint']);
});

it('provides default exclude config values', function () {
    $reviveConfig = new ReviveConfig([
        'paths' => ['path1', 'path2'],
        'lint' => true,
        'fix' => false,
        'using' => ['tlint', 'phpcs', 'php-cs-fixer', 'pint'],
    ]);

    expect($reviveConfig->get('exclude'))->toBe([
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
    ]);
});

it('merges provided exclude with default exclude config values', function () {
    $reviveConfig = new ReviveConfig([
        'paths' => ['path1', 'path2'],
        'lint' => true,
        'fix' => false,
        'using' => ['tlint', 'phpcs', 'php-cs-fixer', 'pint'],
        'exclude' => ['standards'],
    ]);

    expect($reviveConfig->get('exclude'))->toBe([
        'standards/Devanox/Sniffs/PHP/UseConfigOverEnvSniff.php',
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
    ]);
});
