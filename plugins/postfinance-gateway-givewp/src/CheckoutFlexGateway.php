<?php

namespace Thimoo\PostfinanceCheckoutFlex;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\Models\DonationNote;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Http\Response\Types\RedirectResponse;
use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Commands\PaymentComplete;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\Framework\PaymentGateways\PaymentGateway;

// NOTE: https://github.com/impress-org/givewp-example-gateway/blob/master/class-offsite-example-gateway.php

class CheckoutFlexGateway extends PaymentGateway
{
    private int $spaceId;

    private int $clientId;

    private string $secret;

    public $secureRouteMethods = [
        'handleCreatePaymentRedirect',
    ];

    public function __construct()
    {
        $this->spaceId = "123";
        $this->userId = "123";
        $this->secret = "123";
        if (isset($_GET['utm_source'])) {
            setcookie('pfgg_utm_source', $_GET['utm_source']);
        }
        if (isset($_GET['utm_medium'])) {
            setcookie('pfgg_utm_medium', $_GET['utm_medium']);
        }
        if (isset($_GET['utm_campaign'])) {
            setcookie('pfgg_utm_campaign', $_GET['utm_campaign']);
        }
    }

    public static function id(): string
    {
        return 'postfinance-checkout-flex';
    }

    public function getId(): string
    {
        return self::id();
    }

    public function getName(): string
    {
        return 'PostFinance Checkout Flex';
    }

    public function getPaymentMethodLabel(): string
    {
        return 'PostFinance Checkout Flex';
    }

    /**
     * Register a js file to display gateway fields for v3 donation forms
     */
    public function enqueueScript(int $formId)
    {
        wp_enqueue_script(self::id(), plugin_dir_url(__FILE__) . 'js/checkout-flex-gateway.js', ['react', 'wp-element'], '1.0.0', true);
    }

    public function formSettings(int $formId): array
    {
        return [
            'clientKey' => '1234567890'
        ];
    }

    /**
     * @inheritDoc
     */
    public function createPayment(Donation $donation, $gatewayData): GatewayCommand
    {
        try {
            // Step 1: Validate any data passed from the gateway fields in $gatewayData.  Throw the PaymentGatewayException if the data is invalid.
            if (empty($gatewayData['example-gateway-id'])) {
                throw new PaymentGatewayException(__('Example payment ID is required.', 'example-give' ) );
            }

            // Step 2: Create a payment with your gateway.
            $response = $this->exampleRequest(['transaction_id' => $gatewayData['example-gateway-id']]);

            // Step 3: Return a command to complete the donation. You can alternatively return PaymentProcessing for gateways that require a webhook or similar to confirm that the payment is complete. PaymentProcessing will trigger a Payment Processing email notification, configurable in the settings.
            return new PaymentComplete($response['transaction_id']);
        } catch (Exception $e) {
            // Step 4: If an error occurs, you can update the donation status to something appropriate like failed, and finally throw the PaymentGatewayException for the framework to catch the message.
            $errorMessage = $e->getMessage();

            $donation->status = DonationStatus::FAILED();
            $donation->save();

            DonationNote::create([
                'donationId' => $donation->id,
                'content' => sprintf(esc_html__('Donation failed. Reason: %s', 'example-give'), $errorMessage)
            ]);

            throw new PaymentGatewayException($errorMessage);
        }

        // TODO enregistrer l'email donateur dans la DB newsletter
    }

    /**
     * Callback used by PostFinance Checkout Flex platform after SUCCESSFUL payment.
     */
    protected function handleCreatePaymentRedirect(array $queryParams): RedirectResponse
    {
        $donationId = $queryParams['givewp-donation-id'];
        $successUrl = $queryParams['givewp-success-url'];

        $donation = Donation::find($donationId);

        // Complete donation with gateway data
        $donation->status = DonationStatus::COMPLETE();
        $donation->save();

        // error_log('handleCreatePaymentRedirect(queryParams): '.var_export($queryParams, true));
        // error_log('handleCreatePaymentRedirect(GID:'.$donationId.'): '.$successUrl);

        return new RedirectResponse($successUrl);
    }
}
