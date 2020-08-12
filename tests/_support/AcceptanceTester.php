<?php

use Codeception\Actor;
use Helper\Config\FileSytem;
use Helper\Config\Environment;
use Step\Acceptance\PaymentMethod\CreditCardStep;
use Step\Acceptance\PaymentMethod\GenericPaymentMethodStep;
use Step\Acceptance\PaymentMethod\SEPADirectDebitStep;
use Step\Acceptance\ShopSystem\GenericShopSystemStep;
use Step\Acceptance\ShopSystem\PrestashopStep;
use Step\Acceptance\ShopSystem\WoocommerceStep;
use Step\Acceptance\ShopSystem\Magento2Step;

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause()
 *
 * @SuppressWarnings(PHPMD)
 */

/**
 * Class AcceptanceTester
 */
class AcceptanceTester extends Actor
{
    use _generated\AcceptanceTesterActions;

    const CREDIT_CARD = 'creditCard';

    const CREDIT_CARD_ONE_CLICK = 'creditCardOneClick';

    const PAY_PAL = 'payPal';

    const IDEAL = 'iDEAL';

    const GUARANTEED_INVOICE = 'guaranteedInvoice';

    const ALIPAY_CROSS_BORDER = 'alipayCrossBorder';

    const REGISTERED_CUSTOMER = 'registered customer';

    const ADMIN_USER = 'admin user';

    const SOFORT = 'sofort';

    const SOFORTBANKING = 'sofort.';

    const PAYMENT_ON_INVOICE = 'PaymentOnInvoice/PaymentInAdvance';

    const GIROPAY = 'giropay';

    const EPS_ÜBERWEISUNG = 'eps-Überweisung';

    const SEPADIRECTDEBIT = 'sEPADirectDebit';

    //this is used to generate new class instance, so const doesn't work here
    /**
     * @var Environment
     */
    public $env;
    /**
     * @var string
     */
    public $shopInstanceName;
    private $shopInstanceMap = [
        'prestashop' => Step\Acceptance\ShopSystem\PrestashopStep::class,
        'woocommerce' => Step\Acceptance\ShopSystem\WoocommerceStep::class,
        'magento2' => Step\Acceptance\ShopSystem\Magento2Step::class,
    ];
    private $paymentMethodInstanceMap = [
        'CreditCard' => Step\Acceptance\PaymentMethod\CreditCardStep::class,
        'CreditCardOneClick' => Step\Acceptance\PaymentMethod\CreditCardOneClickStep::class,
        'PayPal' => Step\Acceptance\PaymentMethod\PayPalStep::class,
        'iDEAL' => Step\Acceptance\PaymentMethod\IdealStep::class,
        'GuaranteedInvoice' => Step\Acceptance\PaymentMethod\GuaranteedInvoiceStep::class,
        'AlipayCrossBorder' => Step\Acceptance\PaymentMethod\AlipayCrossBorderStep::class,
        'Sofort' => Step\Acceptance\PaymentMethod\SofortStep::class,
        'giropay' => Step\Acceptance\PaymentMethod\GiropayStep::class,
        'eps-Überweisung' => Step\Acceptance\PaymentMethod\EpsStep::class,
        'SEPADirectDebit' => Step\Acceptance\PaymentMethod\SEPADirectDebitStep::class
    ];
    /**
     * @var Actor|PrestashopStep|WoocommerceStep|Magento2Step
     */
    private $shopInstance;

    /**
     * @var
     */
    private $gateway;

    /**
     * @var Actor|CreditCardStep|SEPADirectDebitStep
     */
    private $paymentMethod;

    /**
     * @var
     */
    private $configData;

    /**
     * @Given I initialize shop system
     * @throws Exception
     */
    public function iInitializeShopSystem(): void
    {
        $this->env = new Environment();
        $this->shopInstanceName = $this->env->getEnv()['SHOP_SYSTEM'];
        $this->configData = $this->getDataFromDataFile($this->getFullPath(FileSytem::CONFIG_FILE));
        $this->gateway = $this->configData->gateway;
        if (!$this->shopInstance) {
            $this->shopInstance = $this->createShopSystemInstance($this->shopInstanceName);
        }
    }

    /**
     * @param $shopSystemName
     * @return GenericShopSystemStep
     */
    private function createShopSystemInstance($shopSystemName): GenericShopSystemStep
    {
        if (!$this->isShopSystemSupported($shopSystemName)) {
            throw new RuntimeException(
                'Environment variable SHOP_SYSTEM is not set or requested shop system is not supported'
            );
        }
        /** @var GenericShopSystemStep $shopInstance */
        $shopInstance = new $this->shopInstanceMap[$shopSystemName]($this->getScenario(),
            $this->gateway,
            $this->configData->guest_customer_data,
            $this->configData->registered_customer_data,
            $this->configData->admin_data);
        $shopInstance->configureShopSystemCurrencyAndCountry(
            $this->configData->currency,
            $this->configData->default_country
        );
        $shopInstance->registerCustomer();
        $shopInstance->configureShippingZone(
            $this->configData->shipping_zone_name,
            $this->configData->shipping_zone_region,
            $this->configData->shipping_zone_method,
            $this->configData->shipping_zone_location_type
        );
        return $shopInstance;
    }

    /**
     * @param $shopSystemName
     * @return bool
     */
    private function isShopSystemSupported($shopSystemName): bool
    {
        return array_key_exists($shopSystemName, $this->shopInstanceMap);
    }

    /**
     * @Given I activate :paymentMethod payment action :paymentAction in configuration
     * @param $paymentMethod
     * @param $paymentAction
     * @throws Exception
     */
    public function iActivatePaymentActionInConfiguration($paymentMethod, $paymentAction): void
    {
        $this->shopInstance->configurePaymentMethodCredentials($paymentMethod, $paymentAction);
    }

    /**
     * @Given I prepare checkout with purchase sum :minPurchaseSum in shop system as :arg2
     * @param $minPurchaseSum
     * @param $customerType
     * @throws Exception
     */
    public function iPrepareCheckoutWithPurchaseSumInShopSystemAs($minPurchaseSum, $customerType): void
    {
        if ($customerType === static::REGISTERED_CUSTOMER) {
            $this->shopInstance->logIn();
        }
        $this->shopInstance->fillBasket($minPurchaseSum);
        $this->shopInstance->goToCheckout();
        $this->shopInstance->fillCustomerDetails($customerType);
    }

    /**
     * @Then I see :text
     * @param $text
     */
    public function iSee($text): void
    {
        $this->see($text);
    }

    /**
     * @Then I start :paymentMethod payment
     * @param $paymentMethod
     * @throws Exception
     */
    public function iStartPayment($paymentMethod): void
    {
        $this->shopInstance->startPayment($paymentMethod);
    }

    /**
     * @Given I fill :paymentMethod fields in the shop
     * @param $paymentMethod
     * @throws Exception
     */
    public function iFillFieldsInTheShop($paymentMethod): void
    {
        $this->createPaymentMethodIfNeeded($paymentMethod);
        $this->paymentMethod->fillFieldsInTheShop();
        if (strcasecmp($paymentMethod, static::CREDIT_CARD_ONE_CLICK) !== 0 &&
            strcasecmp($paymentMethod, static::GUARANTEED_INVOICE) !== 0 &&
            strcasecmp($paymentMethod, static::EPS_ÜBERWEISUNG) !== 0 &&
            strcasecmp($paymentMethod, static::SEPADIRECTDEBIT)) {
            $this->shopInstance->proceedWithPayment($paymentMethod);
        }
    }

    /**
     * @param $paymentMethod
     */
    private function createPaymentMethodIfNeeded($paymentMethod): void
    {
        if (!$this->paymentMethodCreated($paymentMethod)) {
            $this->paymentMethod = $this->createPaymentMethod($paymentMethod);
        }
    }

    /**
     * @param $paymentMethod
     * @return bool
     */
    private function paymentMethodCreated($paymentMethod): bool
    {
        if ($this->paymentMethod !== null) {
            return $this->paymentMethod::STEP_NAME === $paymentMethod;
        }
        return false;
    }

    /**
     * @param $paymentMethod
     * @return GenericPaymentMethodStep
     */
    private function createPaymentMethod($paymentMethod): GenericPaymentMethodStep
    {
        //tell which payment method data to use and initialize customer config
        // in locators.json we use payment method names as prefix, like creditcard_data
        $paymentMethodDataName = strtolower($paymentMethod . '_data');
        //all php variables are camel case
        $paymentMethodInstance = new $this->paymentMethodInstanceMap[$paymentMethod](
            $this->getScenario(),
            $this->gateway,
            //all php variables are camel case
            lcfirst($paymentMethod), $this->configData->$paymentMethodDataName);
        return $paymentMethodInstance;
    }

    /**
     * @When I save :paymentMethod for later use
     * @param $paymentMethod
     * @throws Exception
     */
    public function iSaveForLaterUse($paymentMethod): void
    {
        $this->paymentMethod->saveForLaterUse($this->shopInstanceName);
        $this->shopInstance->proceedWithPayment($paymentMethod);
    }

    /**
     * @When I choose :paymentMethod from saved cards list
     * @param $paymentMethod
     * @throws Exception
     */
    public function iChooseFromSavedCardsList($paymentMethod): void
    {
        $this->paymentMethod->chooseCardFromSavedCardsList($this->shopInstanceName);
        $this->shopInstance->proceedWithPayment($paymentMethod);
    }

    /**
     * @Given I perform :paymentMethod actions outside of the shop
     * @param $paymentMethod
     * @throws Exception
     */
    public function iPerformActionsOutsideOfTheShop($paymentMethod): void
    {
        $this->createPaymentMethodIfNeeded($paymentMethod);
        $this->paymentMethod->performPaymentMethodActionsOutsideShop();
    }

    /**
     * @Then I see successful payment
     */
    public function iSeeSuccessfulPayment(): void
    {
        $this->shopInstance->validateSuccessPage();
    }

    /**
     * @Then I see :paymentMethod transaction type :paymentAction in transaction table
     * @param $paymentMethod
     * @param $paymentAction
     */
    public function iSeeTransactionTypeInTransactionTable($paymentMethod, $paymentAction): void
    {
        $this->shopInstance->validateTransactionInDatabase($paymentMethod, $paymentAction);
    }

    /**
     * @return mixed
     */
    public function getEnv()
    {
        return $this->env->getEnv();
    }

    /**
     * @When I place the order and continue :paymentMethod payment
     * @param $paymentMethod
     * @throws Exception
     */
    public function iPlaceTheOrderAndContinuePayment($paymentMethod): void
    {
        $this->shopInstance->placeTheOrder($paymentMethod);
    }

    /**
     * @Given I deactivate :paymentMethod payment method in configuration
     * @param $paymentMethod
     */
    public function iDeactivatePaymentMethodInConfiguration($paymentMethod): void
    {
        $this->shopInstance->deletePaymentMethodFromDb($paymentMethod);
    }

    /**
     * @Then I go into the configuration page as :userType and activate :paymentMethod method
     * @param $userType
     * @param $paymentMethod
     */
    public function iGoIntoTheConfigurationPageAsAndActivateMethod($userType, $paymentMethod): void
    {
        if ($userType === static::ADMIN_USER) {
            $this->shopInstance->logInToAdministrationPanel();
        }
        $this->shopInstance->activatePaymentMethod($paymentMethod);
    }

    /**
     * @When I fill fields with :paymentMethod data for payment action :paymentAction and transaction type :txType
     * @param $paymentMethod
     * @param $paymentAction
     * @param $txType
     */
    public function iFillFieldsWithDataForPaymentActionAndTransactionType($paymentMethod, $paymentAction, $txType): void
    {
        $this->shopInstance->fillPaymentMethodFields($paymentMethod, $paymentAction, $txType);
    }

    /**
     * @Then I see that :paymentMethod payment method is enabled on Payment page
     * @param $paymentMethod
     */
    public function iSeeThatPaymentMethodIsEnabledOnPaymentPage($paymentMethod): void
    {
        $this->shopInstance->goToPaymentPageAndCheckIfPaymentMethodIsEnabled($paymentMethod);
    }

    /**
     * @Then I see all data that was entered is shown in :paymentMethod configuration page
     * @param $paymentMethod
     */
    public function iSeeAllDataThatWasEnteredIsShownInConfigurationPage($paymentMethod): void
    {
        $this->shopInstance->goToConfigurationPageAndCheckIfEnteredDataIsShown($paymentMethod);
    }

    /**
     * @Then I see that test credentials check provides a successful result for :paymentMethod payment method
     * @param $paymentMethod
     */
    public function iSeeThatTestCredentialsCheckProvidesASuccessfulResultForPaymentMethod($paymentMethod): void
    {
        $this->shopInstance->clickOnTestCredentialsAndCheckIfResultIsSuccessful($paymentMethod);
    }

    /**
     * @When I perform additional :paymentMethod payment steps inside the shop
     * @param $paymentMethod
     */
    public function iPerformAdditionalPaymentStepsInsideTheShop($paymentMethod): void
    {
        $this->createPaymentMethodIfNeeded($paymentMethod);
        $this->paymentMethod->performAdditionalPaymentStepsInsideTheShop();
    }

    /**
     * @Given I check values for :paymentMethod and :paymentAction transaction type
     * @param $paymentMethod
     * @param $paymentAction
     */
    public function iNoteTheOrderDetailsAndCheckTransactionValuesInDatabase($paymentMethod, $paymentAction)
    {
        $this->shopInstance->validateTransactionFields($paymentMethod, $paymentAction);
    }
}
