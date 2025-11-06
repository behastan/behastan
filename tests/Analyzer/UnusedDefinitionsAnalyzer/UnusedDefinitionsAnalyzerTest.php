<?php

declare(strict_types=1);

namespace Rector\Behastan\Tests\Analyzer\UnusedDefinitionsAnalyzer;

use Rector\Behastan\Analyzer\UnusedDefinitionsAnalyzer;
use Rector\Behastan\Finder\BehatMetafilesFinder;
use Rector\Behastan\Tests\AbstractTestCase;

final class UnusedDefinitionsAnalyzerTest extends AbstractTestCase
{
    private UnusedDefinitionsAnalyzer $unusedDefinitionsAnalyzer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->unusedDefinitionsAnalyzer = $this->make(UnusedDefinitionsAnalyzer::class);
    }

    public function test(): void
    {
        $featureFiles = BehatMetafilesFinder::findFeatureFiles([__DIR__ . '/Fixture/Features']);
        $this->assertCount(1, $featureFiles);

        $contextFiles = BehatMetafilesFinder::findContextFiles([__DIR__ . '/Fixture/Contexts']);
        $this->assertCount(1, $contextFiles);

        $unusedDefinitions = $this->unusedDefinitionsAnalyzer->analyse($contextFiles, $featureFiles);

        $this->assertCount(0, $unusedDefinitions);
    }
}
