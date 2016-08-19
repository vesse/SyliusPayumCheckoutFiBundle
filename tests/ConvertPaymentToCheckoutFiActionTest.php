<?php

namespace Sylius\Bundle\PayumBundle\CheckoutFi;

use Payum\Core\Action\ActionInterface;
use PHPUnit\Framework\TestCase;
use \Mockery as m;
use Payum\Core\Request\Convert;
use Sylius\Component\Core\Model\PaymentInterface;

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

    public function testDetailsConversion()
    {
        $shouldDetails = array(
            'test'         => true,
            'amount'       => '12345',
            'reference'    => '43121234',
            'deliveryDate' => '20160822',
            'currency'     => 'EUR',
            'firstName'    => 'Eemeli',
            'familyName'   => 'Testimies',
            'address'      => 'Testikuja 14',
            'postCode'     => '33200',
            'postOffice'   => 'Tampere',
            'email'        => 'eemeli.testaaja@example.com',
            'phone'        => '0501234567'
        );

        $validateStamp = function ($processedDetails)
        {
            // Stamp length must be exactly 14 characters & contain only numbers
            return strlen($processedDetails['stamp']) === 14
                && is_numeric($processedDetails['stamp']);
        };

        $action   = m::mock('Sylius\Bundle\PayumBundle\CheckoutFi\Action\ConvertPaymentToCheckoutFiAction[execute]')->makePartial();
        $request  = m::mock('Payum\Core\Request\Convert');
        $payment  = m::mock('Sylius\Component\Core\Model\PaymentInterface');
        $order    = m::mock('order');
        $customer = m::mock('customer');
        $item     = m::mock('item');
        $product  = m::mock('product');
        $items    = m::mock('items');
        $shippingAddress = m::mock('shippingAddress');
        $details = array('test' => true);

        $request->shouldReceive('getSource')          ->times(2)->andReturn($payment);
        $request->shouldReceive('getTo')              ->times(1)->andReturn('array');
        $request->shouldReceive('setResult')          ->times(1)
            ->with(m::subset($shouldDetails))
            ->with(m::on($validateStamp));

        $payment->shouldReceive('getOrder')           ->times(1)->andReturn($order);
        $payment->shouldReceive('getDetails')         ->times(1)->andReturn($details);
        $payment->shouldReceive('getCurrency')        ->times(1)->andReturn('EUR');

        $order->shouldReceive('getTotal')             ->times(1)->andReturn('12345');
        $order->shouldReceive('getId')                ->times(1)->andReturn('43121234');
        $order->shouldReceive('getCustomer')          ->times(7)->andReturn($customer);
        $order->shouldReceive('getItems')             ->times(1)->andReturn($items);

        $customer->shouldReceive('getFirstName')      ->times(1)->andReturn('Eemeli');
        $customer->shouldReceive('getLastName')       ->times(1)->andReturn('Testimies');
        $customer->shouldReceive('getShippingAddress')->times(3)->andReturn($shippingAddress);
        $customer->shouldReceive('getEmail')          ->times(1)->andReturn('eemeli.testaaja@example.com');
        $customer->shouldReceive('getPhoneNumber')    ->times(1)->andReturn('0501234567');

        $shippingAddress->shouldReceive('getStreet')  ->times(1)->andReturn('Testikuja 14');
        $shippingAddress->shouldReceive('getPostcode')->times(1)->andReturn('33200');
        $shippingAddress->shouldReceive('getCity')    ->times(1)->andReturn('Tampere');

        $items->shouldReceive('toArray')              ->times(1)->andReturn([$item, $item, $item]);
        $item->shouldReceive('getProduct')            ->times(6)->andReturn($product);

        $product->shouldReceive('isAvailable')        ->times(3)->andReturn(false);
        $product->shouldReceive('getAvailableOn')     ->times(3)->andReturn(new \DateTime());

        $action->execute($request);
    }

}
