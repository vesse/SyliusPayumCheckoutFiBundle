<?php

namespace Sylius\Bundle\PayumBundle\CheckoutFi;

use PHPUnit\Framework\TestCase;

class ConvertPaymentToCheckoutFiActionTest extends TestCase
{

    public function testCanInstantiate()
    {
        $this->action = new Action\ConvertPaymentToCheckoutFiAction();
        $this->assertInstanceOf(
            'Sylius\Bundle\PayumBundle\CheckoutFi\Action\ConvertPaymentToCheckoutFiAction',
            $this->action
        );
    }
}
