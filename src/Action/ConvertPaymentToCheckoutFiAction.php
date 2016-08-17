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
 *     <tag name="payum.action" context="checkout_fi" />
 * </service>
 */
class ConvertPaymentToCheckoutFiAction implements ActionInterface
{
    private static $REFNUM_FACTORS = [7, 3, 1];

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
        $details['stamp']        = $order->getId();
        $details['amount']       = $order->getTotal();
        $details['reference']    = self::createReferenceFromId($order->getId());
        $details['deliveryDate'] = '20161010'; // TODO
        $details['currency']     = $payment->getCurrency();
        $details['firstName']    = $order->getCustomer()->getFirstName();
        $details['familyName']   = $order->getCustomer()->getLastName();
        $details['address']      = $order->getCustomer()->getShippingAddress()->getStreet();
        $details['postCode']     = $order->getCustomer()->getShippingAddress()->getPostcode();
        $details['postOffice']   = $order->getCustomer()->getShippingAddress()->getCity();
        $details['email']        = $order->getCustomer()->getEmail();
        $details['phone']        = $order->getCustomer()->getPhoneNumber();

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

    private static function createReferenceFromId($id)
    {
        $baseRef = (string) $id + 100;
        return $baseRef . self::calculateReferenceNumberChecksum($baseRef);
    }

    /**
     * Calculate checksum for a Finnish reference number. It is calculated
     * by multiplying individiual digits from right to left by 7, 3, 1, 7, ...
     * and summing the products together, and the result is subtracted from
     * the next ten. e.g. if reference is 1234, the sum of products is
     * 7 * 4 + 3 * 3 + 1 * 2 + 7 * 1 = 46 and thus the checksum is 50 - 46 = 4
     */
    private static function calculateReferenceNumberChecksum($reference)
    {
        $strlen = strlen($reference);
        $product = 0;
        for ($i = 0; $i < $strlen; $i++) {
            $product += substr($reference, $strlen - $i - 1, 1) * self::$REFNUM_FACTORS[$i % 3];
        }

        $nextFullTen = ceil($product / 10) * 10;
        $checksum = $nextFullTen - $product;
        return $checksum == 10 ? 0 : $checksum;
    }
}
