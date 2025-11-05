<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([__DIR__ . '/src', __DIR__ . '/tests'])
    ->withPhpSets()
    ->withPreparedSets(
        codeQuality: true,
        deadCode: true,
        typeDeclarations: true,
        privatization: true,
        earlyReturn: true,
        codingStyle: true,
        instanceOf: true,
        phpunitCodeQuality: true,
        naming: true
    )
    ->withImportNames()
    ->withSkip(['*/scoper.php', '*/Source/*', '*/Fixture/*']);
