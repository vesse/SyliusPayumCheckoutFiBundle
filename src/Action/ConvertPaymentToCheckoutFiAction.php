<?php

namespace Sylius\Bundle\PayumBundle\CheckoutFi\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Convert;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Payment\InvoiceNumberGeneratorInterface;

/**
 * Checkout.fi payment converter for Payum/Sylius.
 *
 * Add this to services.xml:
 *
 * <service id="sylius.payum.checkout.fi.action.convert_payment"
 *          class="Sylius\Bundle\PayumBundle\CheckoutFi\Action\ConvertPaymentToCheckoutFiAction">
 *     <argument type="service" id="sylius.invoice_number_generator" />
 *     <tag name="payum.action" context="checkout_fi" />
 * </service>
 */
class ConvertPaymentToCheckoutFiAction implements ActionInterface
{
    /**
     * @var InvoiceNumberGeneratorInterface
     */
    private $invoiceNumberGenerator;

    /**
     * @param InvoiceNumberGeneratorInterface $invoiceNumberGenerator
     */
    public function __construct(InvoiceNumberGeneratorInterface $invoiceNumberGenerator)
    {
        $this->invoiceNumberGenerator = $invoiceNumberGenerator;
    }

    /**
     * {@inheritdoc}
     *
     * @param Convert $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getSource();
        $order = $payment->getOrder();

        $details = $payment->getDetails();
        $details['stamp'] = $order->getId() . time(); // TODO
        $details['amount'] = $order->getTotal();
        $details['reference'] = '48513821'; // TODO
        $details['deliveryDate'] = '20161010'; // TODO
        $details['currency'] = 'EUR'; //TODO

        $details['firstName'] = $order->getCustomer()->getFirstName();
        $details['lastName'] = $order->getCustomer()->getLastName();
        $details['address'] = 'TODO';
        $details['postCode'] = 'TODO';
        $details['postOffice'] = 'TODO';
        $details['email'] = $order->getCustomer()->getEmail();
        $details['phone'] = $order->getCustomer()->getPhoneNumber();

        $request->setResult($details);
    }

    public function supports($request)
    {
        return
            $request instanceof Convert &&
            $request->getSource() instanceof PaymentInterface &&
            $request->getTo() === 'array'
        ;
    }
}
