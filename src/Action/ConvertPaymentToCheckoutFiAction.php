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
        $details['stamp']        = str_replace(microtime(true), '.', '');
        $details['amount']       = $order->getTotal();
        $details['reference']    = $order->getId();
        $details['deliveryDate'] = self::getDeliveryDate($order);
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

    private static function getDeliveryDate($order) {
        $allItemsAvailableDate = date('Ymd');
        // Items can be shipped only after they are available
        foreach ($order->getItems()->toArray() as $item) {
            if (!$item->getProduct()->isAvailable()) {
                $itemAvailableDate = $item->getProduct()->getAvailableOn()->format('Ymd');
                if ($itemAvailableDate > $allItemsAvailableDate) {
                    $allItemsAvailableDate = $itemAvailableDate;
                }
            }
        }
        // Deliver the next weekday
        $deliveryDate = date('Ymd', strtotime($allItemsAvailableDate . ' +1 Weekday'));
        return $deliveryDate;
    }
}
