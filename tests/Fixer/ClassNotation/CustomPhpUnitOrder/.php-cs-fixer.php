<?php

use App\Fixer\ClassNotation\CustomPhpUnitOrderFixer;
use PhpCsFixer\Config;

return (new Config())
    ->setUsingCache(false)
    ->registerCustomFixers([new CustomPhpUnitOrderFixer])
    ->setRules(['Devanox/custom_phpunit_order' => true]);
