<?php

namespace Sylius\Bundle\PayumBundle\CheckoutFi;

use PHPUnit\Framework\TestCase;
use Sylius\Bundle\PayumBundle\CheckoutFi\Action\ConvertPaymentToCheckoutFiAction;

class CheckoutFiGatewayTest extends TestCase
{
    public function testCanInstantiate()
    {
        $this->action = new ConvertPaymentToCheckoutFiAction();
    }
}
