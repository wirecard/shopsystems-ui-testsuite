Feature: SEPADirectDebitInitialTransactionHappyPath
  As a guest user
  I want to make an initial transaction with SEPA Direct Debit
  And to see that initial transaction was successful

  Background:
    Given I initialize shop system

  @woocommerce
  Scenario Outline: initial transaction
    Given I activate "SEPADirectDebit" payment action <payment_action> in configuration
    And I prepare checkout with purchase sum <amount> in shop system as "guest customer"
    And I see "Wirecard SEPA Direct Debit"
    And I start "SEPADirectDebit" payment
    And I fill "SEPADirectDebit" fields in the shop
    And I place the order and continue "SEPADirectDebit" payment
    When I perform additional "SEPADirectDebit" payment steps inside the shop
    Then I see successful payment
    And I check values for "SEPADirectDebit" and <transaction_type> transaction type
    And I check order state <order_state> in database

    Examples:
      | payment_action  | amount | transaction_type | order_state |
      |    "reserve"    |  "20"  |  "authorization" | on-hold  |
      |      "pay"      |  "20"  |  "debit"         | on-hold  |
