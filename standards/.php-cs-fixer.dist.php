<?php

use App\Fixer\ClassNotation\CustomControllerOrderFixer;
use App\Fixer\ClassNotation\CustomOrderedClassElementsFixer;
use App\Fixer\ClassNotation\CustomPhpUnitOrderFixer;
use App\Support\PhpCsFixer;
use PhpCsFixer\Config;

return (new Config())
    ->setFinder(PhpCsFixer::getFinder())
    ->setUsingCache(false)
    ->registerCustomFixers([
        new CustomControllerOrderFixer(),
        new CustomOrderedClassElementsFixer(),
        new CustomPhpUnitOrderFixer(),
    ])
    ->setRules([
        'Devanox/custom_controller_order' => true,
        'Devanox/custom_ordered_class_elements' => true,
        'Devanox/custom_phpunit_order' => true,
    ]);
