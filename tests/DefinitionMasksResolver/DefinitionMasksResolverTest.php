<?php

declare(strict_types=1);

namespace Rector\Behastan\Tests\DefinitionMasksResolver;

use Rector\Behastan\DefinitionMasksResolver;
use Rector\Behastan\Finder\BehatMetafilesFinder;
use Rector\Behastan\Tests\AbstractTestCase;
use Rector\Behastan\Tests\DefinitionMasksResolver\Fixture\AnotherBehatContext;
use Rector\Behastan\ValueObject\Mask\ExactMask;

final class DefinitionMasksResolverTest extends AbstractTestCase
{
    private DefinitionMasksResolver $definitionMasksResolver;

    private BehatMetafilesFinder $behatMetafilesFinder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->definitionMasksResolver = $this->make(DefinitionMasksResolver::class);

        $this->behatMetafilesFinder = $this->make(BehatMetafilesFinder::class);
    }

    public function test(): void
    {
        $contextFileInfos = $this->behatMetafilesFinder->findContextFiles([__DIR__ . '/Fixture']);
        $maskCollection = $this->definitionMasksResolver->resolve($contextFileInfos);

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
