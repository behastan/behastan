<?php

declare(strict_types=1);

namespace Rector\Behastan\Tests\UsedInstructionResolver;

use Rector\Behastan\Tests\AbstractTestCase;
use Rector\Behastan\UsedInstructionResolver;
use Symfony\Component\Finder\SplFileInfo;

final class UsedInstructionResolverTest extends AbstractTestCase
{
    private UsedInstructionResolver $usedInstructionResolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->usedInstructionResolver = $this->make(UsedInstructionResolver::class);
    }

    public function test(): void
    {
        $fileInfo = new SplFileInfo(__DIR__ . '/Fixture/some_file.feature', '', '');

        $instructions = $this->usedInstructionResolver->resolveInstructionsFromFeatureFiles([$fileInfo]);

        $this->assertSame([
            'I am on the login page',
            'I fill in valid credentials',
            'I should see the dashboard',
        ], $instructions);
    }
}
