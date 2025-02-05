<?php

declare(strict_types=1);

namespace Behastan\Tests\DefinitionMasksResolver\Fixture;

use Behat\Step\Then;

final class AnotherBehatContext
{
    /**
     * @When I click homepage
     */
    public function action(): void
    {
    }

    #[Then('never used')]
    public function deadAction(): void
    {
    }
}
