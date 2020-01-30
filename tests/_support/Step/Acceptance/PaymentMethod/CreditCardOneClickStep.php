<?php


namespace Step\Acceptance\PaymentMethod;


use Exception;
use Facebook\WebDriver\Exception\TimeOutException;

class CreditCardOneClickStep extends CreditCardStep
{
    const STEP_NAME = 'CreditCard';

    /**
     * @return mixed
     * @throws Exception
     */
    public function saveForLaterUse()
    {
        $this->checkOption($this->getLocator()->save_for_later_use);
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function chooseCardFromSavedCardsList()
    {
        $this->preparedClick($this->getLocator()->use_saved_card);
        $this->preparedClick($this->getLocator()->use_card);
        //make sure that credit card form is loaded again and we're ready to proceed
        $this->switchToCreditCardUIFrame();
        $this->waitForText($this->getLocator()->use_different_card);
        //sometimes we need to fill cvv
        try {
            $this->fillField($this->getLocator()->cvv, $this->getPaymentMethod()->getCvv());
        } catch (TimeOutException $e) {
        }
        $this->switchToIFrame();
    }
}