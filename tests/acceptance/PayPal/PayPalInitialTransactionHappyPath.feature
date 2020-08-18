Feature: PayPalInitialTransaction
  As a guest user
  I want to make an initial transaction with PayPal
  And to see that initial transaction was successful

  Background:
    Given I initialize shop system

  @woocommerce @prestashop @magento2 @major @minor @patch
  Scenario Outline: initial transaction
    And I activate "PayPal" payment action <payment_action> in configuration
    And I prepare checkout with purchase sum "100" in shop system as "guest customer"
    And I see "Wirecard PayPal"
    And I start "PayPal" payment
    And I place the order and continue "PayPal" payment
    When I perform "PayPal" actions outside of the shop
    Then I see successful payment
    And I check values for "PayPal" and <transaction_type> transaction type
    And I check order state <order_state> in database

    Examples:
      | payment_action | transaction_type | order_state |
      |      "pay"     |    "purchase"    | processing  |
      |    "reserve"   |  "authorization" | authorized  |
