Feature: User login
    Scenario: Successful login
        Given I am on the login page
        When I login as "Tomas"
        Then The "Tomas" is logged in
