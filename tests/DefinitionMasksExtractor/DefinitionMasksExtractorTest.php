<?php

declare(strict_types=1);

namespace Rector\Behastan\Tests\DefinitionMasksExtractor;

use Rector\Behastan\DefinitionMasksExtractor;
use Rector\Behastan\Finder\BehatMetafilesFinder;
use Rector\Behastan\Tests\AbstractTestCase;
use Rector\Behastan\Tests\DefinitionMasksExtractor\Fixture\AnotherBehatContext;
use Rector\Behastan\ValueObject\Mask\ExactMask;

final class DefinitionMasksExtractorTest extends AbstractTestCase
{
    private DefinitionMasksExtractor $definitionMasksExtractor;

    private BehatMetafilesFinder $behatMetafilesFinder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->definitionMasksExtractor = $this->make(DefinitionMasksExtractor::class);
        $this->behatMetafilesFinder = $this->make(BehatMetafilesFinder::class);
    }

    public function test(): void
    {
        $contextFileInfos = $this->behatMetafilesFinder->findContextFiles([__DIR__ . '/Fixture']);
        $maskCollection = $this->definitionMasksExtractor->extract($contextFileInfos);

        $this->assertCount(3, $maskCollection->all());

        $exactMasks = $maskCollection->byType(ExactMask::class);
        $this->assertCount(3, $exactMasks);
        $this->assertContainsOnlyInstancesOf(ExactMask::class, $exactMasks);

        $firstExactMask = $exactMasks[0];

        $this->assertSame('I click homepage', $firstExactMask->mask);
        $this->assertSame(AnotherBehatContext::class, $firstExactMask->className);
        $this->assertSame(__DIR__ . '/Fixture/AnotherBehatContext.php', $firstExactMask->filePath);

        $slashMask = $exactMasks[2];

        $this->assertSame('Do this and / that', $slashMask->mask);
    }
}
