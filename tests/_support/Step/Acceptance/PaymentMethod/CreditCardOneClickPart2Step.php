<?php


namespace Step\Acceptance\PaymentMethod;


use Exception;

class CreditCardOneClickPart2Step extends CreditCardStep
{
    const STEP_NAME = 'CreditCard';

    /**
     * @return mixed
     * @throws Exception
     */
    public function performPaymentActionsInTheShop()
    {
        $this->preparedClick($this->getLocator()->use_saved_card);
        $this->preparedClick($this->getLocator()->use_card);
    }
}