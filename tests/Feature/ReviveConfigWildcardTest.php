<?php

it('allows wildcard includes and excludes', function () {
    chdir(__DIR__ . '/../Fixtures/ReviveConfigWildcard');

    [$statusCode, $output] = run('lint', [
        'path' => base_path('tests/Fixtures/ReviveConfigWildcard'),
    ]);

    expect($statusCode)->toBe(1)
        ->and($output)
        ->toContain('Class members of differing visibility')
        ->toContain('Class name doesn\'t match filename')
        ->toContain('Devanox/custom_ordered_class_elements')
        ->toContain('concat_space')
        ->not->toContain('ExcludeClass.php');
});
