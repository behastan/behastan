<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

$nowDateTime = new DateTime('now');
$timestamp = $nowDateTime->format('Ym');

// @see https://github.com/humbug/php-scoper/blob/master/docs/further-reading.md
use Nette\Utils\Strings;

// see https://github.com/humbug/php-scoper
return [
    'prefix' => 'Behastan' . $timestamp,
    'expose-constants' => ['#^SYMFONY\_[\p{L}_]+$#'],
    'exclude-classes' => [\Symfony\Component\Finder\SplFileInfo::class],
    'exclude-namespaces' => [
        '#^Behastan\\\\#',
        '#^Symfony\\\\Polyfill#'
    ],
    'exclude-files' => [
        // do not prefix "trigger_deprecation" from symfony - https://github.com/symfony/symfony/commit/0032b2a2893d3be592d4312b7b098fb9d71aca03
        // these paths are relative to this file location, so it should be in the root directory
        'vendor/symfony/deprecation-contracts/function.php',
    ],
];
