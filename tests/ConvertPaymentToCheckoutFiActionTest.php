<?php

namespace Sylius\Bundle\PayumBundle\CheckoutFi;

use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Payment\IdBasedInvoiceNumberGenerator;

class ConvertPaymentToCheckoutFiActionTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->generator = new IdBasedInvoiceNumberGenerator;
    }

    public function testCanInstantiate()
    {
        $this->action = new Action\ConvertPaymentToCheckoutFiAction($this->generator);
        $this->assertInstanceOf(
            'Sylius\Bundle\PayumBundle\CheckoutFi\Action\ConvertPaymentToCheckoutFiAction',
            $this->action
        );
    }
}
