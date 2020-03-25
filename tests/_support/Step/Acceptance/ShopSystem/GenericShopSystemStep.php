<?php

namespace Step\Acceptance\ShopSystem;

use Codeception\Scenario;
use Exception;
use Helper\Config\Customer\CustomerConfig;
use Helper\Config\FileSytem;
use Step\Acceptance\GenericStep;

/**
 * Class GenericShopSystemStep
 * @package Step\Acceptance|ShopSystem
 */
class GenericShopSystemStep extends GenericStep
{
    /**
     * @var CustomerConfig;
     */
    private $guestCustomer;

    /**
     * @var CustomerConfig;
     */
    private $registeredCustomer;

    /**
     * @var array
     */
    private $mappedPaymentActions = [
        'CreditCard' => [
            'config' => [
                'row' => 'payment_action',
                'reserve' => 'reserve',
                'pay' => 'pay'
            ],
            'tx_table' => [
                'authorization' => 'authorization',
                'purchase' => 'purchase'
            ]
        ],
        'PayPal' => [
            'config' => [
                'row' => 'payment_action',
                'reserve' => 'reserve',
                'pay' => 'pay'
            ],
            'tx_table' => [
                'authorization' => 'authorization',
                'purchase' => 'debit'
            ]
        ]
    ];

    /**
     * @var array
     */
    private $redirectPaymentMethods = ['PayPal'];

    /**
     * GenericStep constructor.
     * @param Scenario $scenario
     * @param $gateway
     * @param $guestFileName
     * @param $registeredFileName
     */
    public function __construct(Scenario $scenario, $gateway, $guestFileName, $registeredFileName)
    {
        parent::__construct($scenario, $gateway);
        $this->setLocator($this->getDataFromDataFile($this->getFullPath(FileSytem::SHOP_SYSTEM_LOCATOR_FOLDER_PATH . static::STEP_NAME . DIRECTORY_SEPARATOR . static::STEP_NAME . 'Locators.json')));
        $this->createCustomerObjects($guestFileName, $registeredFileName);
    }

    /**
     * @param $guestFileName
     * @param $registeredFileName
     */
    public function createCustomerObjects($guestFileName, $registeredFileName): void
    {
        $dataFolderPath = $this->getFullPath(FileSytem::CUSTOMER_DATA_FOLDER_PATH);
        $this->guestCustomer = new CustomerConfig($this->getDataFromDataFile($dataFolderPath . $guestFileName));
        $this->registeredCustomer = new CustomerConfig($this->getDataFromDataFile($dataFolderPath . $registeredFileName));
    }

    /**
     * @param $name
     * @param $value
     */
    public function putValueInDatabase($name, $value): void
    {
        if (!$this->existsInDatabase($name)) {
            $this->haveInDatabase(static::SETTINGS_TABLE_NAME,
                [static::NAME_COLUMN_NAME => $name,
                    static::VALUE_COLUMN_NAME => $value]);
        } else {
            $this->updateInDatabase(static::SETTINGS_TABLE_NAME,
                [static::VALUE_COLUMN_NAME => $value],
                [static::NAME_COLUMN_NAME => $name]
            );
        }
    }

    /**
     * @param String $currency
     * @param String $defaultCountry
     */
    public function configureShopSystemCurrencyAndCountry($currency, $defaultCountry): void
    {
        $this->putValueInDatabase(static::CURRENCY_OPTION_NAME, $currency);
        $this->putValueInDatabase(static::DEFAULT_COUNTRY_OPTION_NAME, $defaultCountry);
    }

    /**
     * @param String $minPurchaseSum
     * @throws Exception
     */
    public function fillBasket($minPurchaseSum): void
    {
        $this->amOnPage($this->getLocator()->page->product);

        $amount = intdiv((int)$minPurchaseSum, (int)$this->getLocator()->product->price) + 1;
        //add to basket goods to fulfill desired purchase amount
        $this->fillField($this->getLocator()->product->quantity, $amount);
        $this->preparedClick($this->getLocator()->product->add_to_cart);
    }

    /**
     * @return mixed
     */
    public function goToCheckout() : void
    {
        $this->amOnPage($this->getLocator()->page->checkout);
    }

    /**
     *
     */
    public function validateSuccessPage(): void
    {
        $this->waitUntil(60, [$this, 'waitUntilPageLoaded'], [$this->getLocator()->page->order_received]);
        $this->see($this->getLocator()->order_received->order_confirmed_message);
    }

    /**
     * @param $paymentMethod
     * @param $paymentAction
     */
    public function validateTransactionInDatabase($paymentMethod, $paymentAction): void
    {
        $this->waitUntil(80, [$this, 'checkPaymentActionInTransactionTable'], [$paymentMethod, $paymentAction]);
        $this->assertEquals($this->checkPaymentActionInTransactionTable([$paymentMethod, $paymentAction]), true);
    }

    /**
     * @param array $paymentArgs
     * @return bool
     */
    public function checkPaymentActionInTransactionTable($paymentArgs): bool
    {
        $transactionTypes = $this->getColumnFromDatabaseNoCriteria(static::TRANSACTION_TABLE_NAME, static::TRANSACTION_TYPE_COLUMN_NAME);
        $tempTxType = $this->selectTxTypeFromMappedPaymentActions($paymentArgs);
        codecept_debug($tempTxType);
        return end($transactionTypes) === $tempTxType;
    }

    /**
     * @throws ExceptionAlias
     */
    public function logIn()
    {
        $this->amOnPage($this->getLocator()->page->sign_in);
        if (!$this->isCustomerSignedIn()) {
            $this->preparedFillField($this->getLocator()->sign_in->email, $this->getCustomer(static::REGISTERED_CUSTOMER)->getEmailAddress());
            $this->preparedFillField($this->getLocator()->sign_in->password, $this->getCustomer(static::REGISTERED_CUSTOMER)->getPassword());
            $this->preparedClick($this->getLocator()->sign_in->sign_in, 60);
        }
    }

    /**
     * @return array
     */
    public function getMappedPaymentActions(): array
    {
        return $this->mappedPaymentActions;
    }

    /**
     * @return array
     */
    public function getRedirectPaymentMethods(): array
    {
        return $this->redirectPaymentMethods;
    }

    /**
     * @param String $name
     * @return mixed
     */
    public function existsInDatabase($name)
    {
        return $this->grabFromDatabase(static::SETTINGS_TABLE_NAME, static::NAME_COLUMN_NAME, [static::NAME_COLUMN_NAME => $name]);
    }

    /**
     * @param String $paymentMethod
     * @return bool
     */
    public function isRedirectPaymentMethod($paymentMethod): bool
    {
        return in_array($paymentMethod, $this->getRedirectPaymentMethods(), false);
    }

    /**
     * @param $customerType
     * @return mixed
     */
    public function getCustomer($customerType)
    {
        if ($customerType === static::REGISTERED_CUSTOMER)
        {
            return $this->registeredCustomer;
        }
        return $this->guestCustomer;
    }

    /**
     * @param string $paymentMethod
     * @return mixed
     */
    public function getMappedTxTableValuesForPaymentMethod($paymentMethod)
    {
        return $this->getMappedPaymentActions()[$paymentMethod]['tx_table'];
    }


    /**
     * @param array $paymentArgs
     * @return mixed
     */
    public function selectTxTypeFromMappedPaymentActions($paymentArgs)
    {
        return $this->getMappedTxTableValuesForPaymentMethod($paymentArgs[0])[$paymentArgs[1]];
    }

    /**
     * @return bool
     */
    public function isCustomerRegistered(): bool
    {
        $guest = $this->grabFromDatabase(static::CUSTOMER_TABLE, static::CUSTOMER_EMAIL_COLUMN_NAME,
            [static::CUSTOMER_EMAIL_COLUMN_NAME => $this->getCustomer(static::REGISTERED_CUSTOMER)->getEmailAddress()]);
        return $guest === '0';
    }
}
