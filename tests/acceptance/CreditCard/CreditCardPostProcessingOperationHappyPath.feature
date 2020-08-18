Feature: CreditCardInitialTransactionHappyPath
  As a guest user
  I want to make an initial transaction with a Credit Card
  And to see that initial transaction was successful

  Background:
    Given I initialize shop system
    And I activate "CreditCard" payment action <payment_action> in configuration
    And I prepare checkout with purchase sum <amount> in shop system as "guest customer"
    And I see "Wirecard Credit Card"
    And I start "CreditCard" payment
    And I place the order and continue "CreditCard" payment
    And I fill "CreditCard" fields in the shop
    And I perform "CreditCard" actions outside of the shop
    And I see successful payment
    And I see "CreditCard" transaction type <transaction_type> in transaction table

  @woocommerce @major @minor @patch
  Scenario Outline: initial transaction 3DS
    Given I go into the configuration page as "admin user"
    And I check "CreditCard" transaction type <transaction_type> in backend transaction table
    When I preform post-processing operation <post_proc_operation>
    Then I see "CreditCard" transaction type <post_proc_transaction_type> in transaction table
    And And I check "CreditCard" transaction type <post_proc_transaction_type> in backend transaction table


    Examples:
      | payment_action  | amount | transaction_type | post_proc_operation| post_proc_transaction_type |
      |      "pay"      |  "100" |    "purchase"    |   "refund"         |          "refund-purchase" |
