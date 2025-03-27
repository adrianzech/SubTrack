<?php

declare(strict_types=1);

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
    ->exclude('vendor')
    ->exclude('public/build')
    ->exclude('assets/vendor')
    ->notPath('src/Kernel.php')
    ->notPath('public/index.php')
    ->name('*.php');

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        'declare_strict_types' => true,
        'yoda_style' => false, // Personal preference, Symfony standard enables Yoda style ($var === null), set to false to prefer (null === $var) or ($var === 5)
        'concat_space' => ['spacing' => 'one'], // Ensure single space around concatenation '.'
    ])
    ->setFinder($finder)
    ->setRiskyAllowed(true) // Allow rules like declare_strict_types which can slightly change code behavior
    ->setUsingCache(true); // Speed up subsequent runs
