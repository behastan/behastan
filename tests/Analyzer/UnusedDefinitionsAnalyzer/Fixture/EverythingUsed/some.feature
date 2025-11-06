Feature: User login
    Scenario: Successful login
        Given I am on the login page
        When I login as "Tomas"
        When I login with "tomas.me@gmail.com" email
        Then The "Tomas" is logged in
