<?php

it('allows wildcard includes and excludes', function () {
    chdir(__DIR__ . '/../Fixtures/DusterConfigWildcard');

    [$statusCode, $output] = run('lint', [
        'path' => base_path('tests/Fixtures/DusterConfigWildcard'),
    ]);

    expect($statusCode)->toBe(1)
        ->and($output)
        ->toContain('Class members of differing visibility')
        ->toContain('Filename doesn\'t match class name')
        ->toContain('Tighten/custom_ordered_class_elements')
        ->toContain('concat_space')
        ->not->toContain('ExcludeClass.php');
});
