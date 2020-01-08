<?php


namespace Step\Acceptance\PaymentMethod;


use Exception;

class CreditCardOneClickPart1Step extends CreditCardStep
{
    const STEP_NAME = 'CreditCard';

    /**
     * @return mixed
     * @throws Exception
     */
    public function performPaymentActionsInTheShop()
    {
        parent::performPaymentActionsInTheShop();
        $this->checkOption($this->getLocator()->save_for_later_use);
    }
}
