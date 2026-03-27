<?php

use App\Fixer\ClassNotation\CustomControllerOrderFixer;
use PhpCsFixer\Config;

return (new Config())
    ->setUsingCache(false)
    ->registerCustomFixers([new CustomControllerOrderFixer()])
    ->setRules(['Devanox/custom_controller_order' => true]);
