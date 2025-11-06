<?php

namespace Rector\Behastan\Tests\Analyzer\UnusedDefinitionsAnalyzer\Fixture\EverythingUsed;

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Behat\Step\When;

final class LoginContext implements Context
{
    #[Given('I am on the login page')]
    public function iAmOnTheLoginPage(): void
    {
    }

    #[When('I login as :username')]
    public function iLoginAs(string $username): void
    {
    }

    #[When('I login with :email email')]
    public function iLoginWith(string $email): void
    {
    }

    #[When('The :username is logged in')]
    public function isLoggedIn(string $username): void
    {
    }
}
