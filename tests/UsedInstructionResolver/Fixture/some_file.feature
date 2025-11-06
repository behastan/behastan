Feature: User login
    Scenario: Successful login
        Given I am on the login page
        When I fill in valid credentials
        Then I should see the dashboard
