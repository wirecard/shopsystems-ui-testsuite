Feature: CreditCard3DSOneClickHappyPath
  As a registered user
  I want to make a one-click checkout with a Credit Card 3DS
  And to see that transaction was successful


Scenario: authorize
    Given I initialize shop system
    And I activate "CreditCardOneClick" payment action "reserve" in configuration
    And I prepare checkout with purchase sum "100" in shop system as "registered customer"
#  Background:
#    Given I initialize shop system
#    And I activate "CreditCardOneClick" payment action "reserve" in configuration
#    #And I prepare checkout with purchase sum "100" in shop system as "guest"
#    And I prepare checkout with purchase sum "100" in shop system as "registered customer"
#    Then I see "Wirecard Credit Card"
#    And I start "CreditCard" payment
#
#  @patch @minor @major
#  Scenario: authorize
#    Given I perform "CreditCardOneClick" payment actions in the shop
#    And I perform payment method actions outside of the shop
#    And I see successful payment
#    When I prepare checkout with purchase sum "100" in shop system as "registered customer"
#    And I perform "CreditCardOneClick" payment actions in the shop
#    And I perform payment method actions outside of the shop
#    Then I see successful payment
#    And I see "CreditCard" transaction type "authorization" in transaction table
