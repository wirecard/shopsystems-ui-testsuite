<?php


namespace Step\Acceptance\PaymentMethod;


use Exception;

class CreditCardOneClickStep extends CreditCardStep
{
    const STEP_NAME = 'CreditCard';

    /**
     * @return mixed
     * @throws Exception
     */
    public function performPaymentActionsInTheShop()
    {
        $this->pause();
        parent::performPaymentActionsInTheShop();
        $this->checkOption($this->getLocator()->save_for_later_use);
    }
}
