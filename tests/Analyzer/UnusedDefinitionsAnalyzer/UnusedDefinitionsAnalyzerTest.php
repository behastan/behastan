<?php

declare(strict_types=1);

namespace Rector\Behastan\Tests\Analyzer\UnusedDefinitionsAnalyzer;

use Rector\Behastan\Analyzer\UnusedDefinitionsAnalyzer;
use Rector\Behastan\Finder\BehatMetafilesFinder;
use Rector\Behastan\Tests\AbstractTestCase;
use Rector\Behastan\ValueObject\Mask\AbstractMask;

final class UnusedDefinitionsAnalyzerTest extends AbstractTestCase
{
    private UnusedDefinitionsAnalyzer $unusedDefinitionsAnalyzer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->unusedDefinitionsAnalyzer = $this->make(UnusedDefinitionsAnalyzer::class);
    }

    public function testEverythingUsed(): void
    {
        $featureFiles = BehatMetafilesFinder::findFeatureFiles([__DIR__ . '/Fixture/EverythingUsed']);
        $contextFiles = BehatMetafilesFinder::findContextFiles([__DIR__ . '/Fixture/EverythingUsed']);

        $this->assertCount(1, $featureFiles);
        $this->assertCount(1, $contextFiles);

        $unusedDefinitions = $this->unusedDefinitionsAnalyzer->analyse($contextFiles, $featureFiles);

        $this->assertCount(0, $unusedDefinitions);
    }

    public function testFoundMask(): void
    {
        $featureFiles = BehatMetafilesFinder::findFeatureFiles([__DIR__ . '/Fixture/UnusedMasks']);
        $contextFiles = BehatMetafilesFinder::findContextFiles([__DIR__ . '/Fixture/UnusedMasks']);

        $this->assertCount(1, $featureFiles);
        $this->assertCount(1, $contextFiles);

        $unusedMasks = $this->unusedDefinitionsAnalyzer->analyse($contextFiles, $featureFiles);
        $this->assertCount(1, $unusedMasks);
        $this->assertContainsOnlyInstancesOf(AbstractMask::class, $unusedMasks);

        /** @var AbstractMask $unusedMask */
        $unusedMask = $unusedMasks[0];
        $this->assertSame(__DIR__ . '/Fixture/UnusedMasks/BehatContext.php', $unusedMask->filePath);
        $this->assertSame('never used', $unusedMask->mask);
    }
}
