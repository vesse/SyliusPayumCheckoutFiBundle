# SyliusPayumCheckoutFiBundle

Payment conversion action from Sylius/Payum to [omnipay-checkout.fi](https://github.com/vesse/omnipay-checkout.fi) gateway

## Usage

Define the conversion in `services.xml`:

```xml
<service id="sylius.payum.checkout.fi.action.convert_payment"
         class="Sylius\Bundle\PayumBundle\CheckoutFi\Action\ConvertPaymentToCheckoutFiAction">
    <argument type="service" id="sylius.invoice_number_generator" />
    <tag name="payum.action" context="checkout_fi" />
</service>
```

Define the gateway in `config.yml`:

```yaml
payum:
    gateways:
        checkout_fi:
            omnipay_offsite:
                type: CheckoutFi
                options:
                    merchantId: 375917
                    merchantSecret: SAIPPUAKAUPPIAS
                actions:
                    - sylius.payum.checkout.fi.action.convert_payment
sylius_payment:
    gateways:
        checkout_fi: Checkout.fi
```


## Development

```bash
composer install
composer dump-autoload
composer test
```
