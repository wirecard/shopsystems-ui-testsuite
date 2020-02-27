Feature: CreditCardHappyPath
  As a guest  user
  I want to make an Authorization and Purchase with a Credit Card
  And to see that transaction was successful

  Background:
    Given I initialize shop system

  @woocommerce @prestashop
  Scenario Outline: transactionNon3DS
    Given I activate "CreditCard" payment action <payment_action> in configuration
    And I prepare checkout with purchase sum <amount> in shop system as "guest customer"
    And I see "Wirecard Credit Card"
    And I start "CreditCard" payment
    When I fill "CreditCard" fields in the shop
    Then I see successful payment
    And I see "CreditCard" transaction type <transaction_type> in transaction table

    Examples:
      | payment_action  | amount | transaction_type |
      |    "reserve"    |  "10"  |  "authorization" |
      |      "pay"      |  "10"  |    "purchase"    |

  @woocommerce @prestashop
  Scenario Outline: transaction3DS
    Given I activate "CreditCard" payment action <payment_action> in configuration
    And I prepare checkout with purchase sum <amount> in shop system as "guest customer"
    And I see "Wirecard Credit Card"
    And I start "CreditCard" payment
    When I fill "CreditCard" fields in the shop
    And I perform "CreditCard" actions outside of the shop
    Then I see successful payment
    And I see "CreditCard" transaction type <transaction_type> in transaction table

    Examples:
      | payment_action  | amount | transaction_type |
      |    "reserve"    |  "100" |  "authorization" |
      |      "pay"      |  "100" |    "purchase"    |
