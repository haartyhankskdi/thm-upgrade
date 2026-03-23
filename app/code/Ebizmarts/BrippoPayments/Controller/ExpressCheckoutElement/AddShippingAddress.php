<?php

namespace Ebizmarts\BrippoPayments\Controller\ExpressCheckoutElement;

use Ebizmarts\BrippoPayments\Helper\Stripe;
use Ebizmarts\BrippoPayments\Helper\ExpressCheckoutElement;
use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Ebizmarts\BrippoPayments\Helper\Logger;
use Ebizmarts\BrippoPayments\Helper\Data as DataHelper;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;

class AddShippingAddress extends Action
{
    protected $jsonFactory;
    protected $logger;
    protected $dataHelper;
    protected $checkoutSession;
    protected $eceHelper;
    protected $stripeHelper;
    protected $storeManager;

    /**
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param Logger $logger
     * @param DataHelper $dataHelper
     * @param CheckoutSession $checkoutSession
     * @param ExpressCheckoutElement $eceHelper
     * @param Stripe $stripeHelper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context                             $context,
        JsonFactory                         $jsonFactory,
        Logger                              $logger,
        DataHelper                          $dataHelper,
        CheckoutSession                     $checkoutSession,
        ExpressCheckoutElement              $eceHelper,
        Stripe                              $stripeHelper,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->logger = $logger;
        $this->dataHelper = $dataHelper;
        $this->checkoutSession = $checkoutSession;
        $this->eceHelper = $eceHelper;
        $this->stripeHelper = $stripeHelper;
        $this->storeManager = $storeManager;
    }

    public function execute()
    {
        try {
            $this->eceHelper->setParamsFromRequestBody($this->getRequest());
            $shippingAddress = $this->getRequest()->getParam('shippingAddress');
            $selectedDeliveryOption = $this->getRequest()->getParam('selectedDeliveryOption');
            $quote = $this->checkoutSession->getQuote();
            $scopeId = $this->storeManager->getStore()->getId();
            $currency = $this->eceHelper->getModalCurrencyFromQuote($quote, $scopeId);

            $this->eceHelper->setShippingAddressToQuoteFromPaymentRequestData(
                $quote,
                $shippingAddress,
                $currency
            );

            if ($this->eceHelper->isGBPostalCodeAndNeedsFixing($shippingAddress)) {
                $shippingAddress = $this->eceHelper->fixGBPostalCode($shippingAddress);
                $this->eceHelper->setShippingAddressToQuoteFromPaymentRequestData(
                    $quote,
                    $shippingAddress,
                    $currency
                );
            }
            $shippingOptionsToSend = $this->eceHelper->getShippingOptions($quote, $currency);

            if (empty($shippingOptionsToSend)) {
                // phpcs:disable
                $this->logger->log(print_r($shippingAddress, true));
                // phpcs:enable
                //toDo revert to previous address as no shipping address event will be thrown again
                throw new LocalizedException(__('No shipping options available for your address.'));
            }

            // Handle selected delivery option from frontend
            $shippingMethodToSet = null;
            if (!empty($selectedDeliveryOption)) {
                if ($this->eceHelper->isShippingMethodAvailable($shippingOptionsToSend, $selectedDeliveryOption)) {
                    $shippingMethodToSet = $selectedDeliveryOption;
                }
            }

            if (empty($quote->getShippingAddress()->getShippingMethod())) {
                if ($shippingMethodToSet) {
                    $this->eceHelper->setShippingMethodToQuote($quote, $shippingMethodToSet);
                } elseif (isset($shippingOptionsToSend[0]['id'])) {
                    $this->logger->log("No shipping method is set, setting shipping method to default: "
                        . $shippingOptionsToSend[0]['id']);
                    $this->eceHelper->setShippingMethodToQuote($quote, $shippingOptionsToSend[0]['id']);
                }
            } elseif ($shippingMethodToSet && $quote->getShippingAddress()->getShippingMethod() !== $shippingMethodToSet) {
                // Update shipping method if frontend selection is different from current
                $this->eceHelper->setShippingMethodToQuote($quote, $shippingMethodToSet);
            }
            $shippingOptionsToSend = $this->eceHelper->getShippingOptions($quote, $currency);

            $response = [
                'valid' => 1,
                'options' => [
                    'amount' => $this->stripeHelper->convertMagentoAmountToStripeAmount(
                        $quote->getGrandTotal(),
                        strtolower($currency)
                    )
                ],
                'checkoutOptions' => [
                    'lineItems' => $this->eceHelper->getLineItems($quote, strtolower($currency)),
                    'shippingRates' => $shippingOptionsToSend
                ]
            ];
        } catch (Exception $ex) {
            $error = $this->eceHelper->prettifyErrorMessage($ex->getMessage(), [], $shippingAddress ?? []);
            $this->logger->log($error);
            $this->logger->log(print_r($shippingAddress ?? [], true));
            $response = [
                'valid' => 0,
                'message' => $error
            ];
        }

        $resultJson = $this->jsonFactory->create();
        $resultJson->setData($response);
        return $resultJson;
    }
}
