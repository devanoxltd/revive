<?php

it('uses revive config file', function () {
    chdir(__DIR__ . '/../Fixtures/ReviveConfig');

    [$statusCode, $output] = run('lint', [
        'path' => base_path('tests/Fixtures/ReviveConfig'),
    ]);

    expect($statusCode)->toBe(1)
        ->and($output)
        ->toContain('Class members of differing visibility must be separated by a blank line')
        ->toContain('Class name doesn\'t match filename')
        ->toContain('Devanox/custom_ordered_class_elements')
        ->toContain('concat_space')
        ->not->toContain('ExcludeClass.php');
});
