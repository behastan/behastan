<?php

namespace Analyzer\UnusedDefinitionsAnalyzer\Fixture\Contexts;

use Behat\Behat\Context\Context;
use Behat\Step\Given;
use Behat\Step\When;

final class LoginContext implements Context
{
    #[Given('I am on the login page')]
    public function iAmOnTheLoginPage(): void
    {
        // open login page
    }

    #[When('I login as :username')]
    public function iLoginAs(string $username): void
    {
        // fill credentials and submit form
    }

    #[When('The :username is logged in')]
    public function isLoggedIn(string $username): void
    {
        // fill credentials and submit form
    }
}
