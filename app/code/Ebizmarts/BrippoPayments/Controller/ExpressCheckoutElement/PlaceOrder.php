<?php

namespace Ebizmarts\BrippoPayments\Controller\ExpressCheckoutElement;

use Ebizmarts\BrippoPayments\Helper\ConnectedAccounts;
use Ebizmarts\BrippoPayments\Helper\Data as DataHelper;
use Ebizmarts\BrippoPayments\Helper\Payments as PaymentsHelper;
use Ebizmarts\BrippoPayments\Helper\Stripe;
use Ebizmarts\BrippoPayments\Model\BrippoOrder;
use Ebizmarts\BrippoPayments\Helper\ExpressCheckoutElement;
use Exception;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Ebizmarts\BrippoPayments\Helper\Logger;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Model\QuoteFactory;
use Ebizmarts\BrippoPayments\Helper\BrippoApi\PaymentIntents as BrippoPaymentIntentsApi;
use Magento\Store\Model\StoreManagerInterface;

class PlaceOrder extends Action
{
    protected $logger;
    protected $quoteManagement;
    protected $checkoutSession;
    protected $quoteFactory;
    protected $brippoApiPaymentIntents;
    protected $jsonFactory;
    protected $eceHelper;
    protected $storeManager;
    protected $dataHelper;
    protected $paymentsHelper;
    protected $stripeHelper;
    protected $connectedAccountsHelper;

    /** @var EventManager */
    protected $eventManager;

    /**
     * @param Context $context
     * @param Logger $logger
     * @param CartManagementInterface $quoteManagement
     * @param CheckoutSession $checkoutSession
     * @param QuoteFactory $quoteFactory
     * @param BrippoPaymentIntentsApi $brippoApiPaymentIntents
     * @param JsonFactory $jsonFactory
     * @param ExpressCheckoutElement $eceHelper
     * @param StoreManagerInterface $storeManager
     * @param DataHelper $dataHelper
     * @param PaymentsHelper $paymentsHelper
     * @param Stripe $stripeHelper
     * @param ConnectedAccounts $connectedAccountsHelper
     * @param EventManager $eventManager
     */
    public function __construct(
        Context                 $context,
        Logger                  $logger,
        CartManagementInterface $quoteManagement,
        CheckoutSession         $checkoutSession,
        QuoteFactory            $quoteFactory,
        BrippoPaymentIntentsApi $brippoApiPaymentIntents,
        JsonFactory             $jsonFactory,
        ExpressCheckoutElement  $eceHelper,
        StoreManagerInterface   $storeManager,
        DataHelper              $dataHelper,
        PaymentsHelper          $paymentsHelper,
        Stripe                  $stripeHelper,
        ConnectedAccounts       $connectedAccountsHelper,
        EventManager            $eventManager
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->quoteManagement = $quoteManagement;
        $this->checkoutSession = $checkoutSession;
        $this->quoteFactory = $quoteFactory;
        $this->brippoApiPaymentIntents = $brippoApiPaymentIntents;
        $this->jsonFactory = $jsonFactory;
        $this->eceHelper = $eceHelper;
        $this->storeManager = $storeManager;
        $this->dataHelper = $dataHelper;
        $this->paymentsHelper = $paymentsHelper;
        $this->stripeHelper = $stripeHelper;
        $this->connectedAccountsHelper = $connectedAccountsHelper;
        $this->eventManager = $eventManager;
    }

    public function execute()
    {
        $this->eceHelper->setParamsFromRequestBody($this->getRequest());
        $scopeId = $this->storeManager->getStore()->getId();
        $paymentMethod = $this->getRequest()->getParam('paymentMethod');
        $shippingAddress = $this->getRequest()->getParam('shippingAddress');
        $billingDetails = $this->getRequest()->getParam('billingDetails');
        $placementId = $this->getRequest()->getParam('placementId');
        $payerName = $paymentMethod['billing_details']['name']??'UNKNOWN';
        $walletEmail = $paymentMethod['billing_details']['email']??'UNKNOWN';
        $payerPhone = $paymentMethod['billing_details']['phone']??'UNKNOWN';
        $provider = $this->stripeHelper->getWalletNameFromPaymentMethod($paymentMethod);
        $card = $this->stripeHelper->getCardFromPaymentMethod($paymentMethod);
        $pickupInputValues = $this->getRequest()->getParam('pickupInputValues');
        $checkoutQuoteBillingAddress = $this->getRequest()->getParam('checkoutBillingAddress');
        $checkoutQuoteShippingAddress = $this->getRequest()->getParam('checkoutShippingAddress');
        $checkoutShippingMethod = $this->getRequest()->getParam('checkoutShippingMethod');
        $checkoutEmail = $this->getRequest()->getParam('checkoutEmail');

        $this->logger->log('Trying to place order for ' . $payerName . ' with email address: ' . $walletEmail . '.');

        try {
            $quote = $this->checkoutSession->getQuote();

            if (empty($quote->getId())) {
                throw new LocalizedException(__('Your cart is empty, please refresh the page and try again.'));
            }

            $currency = $this->eceHelper->getPaymentIntentCurrencyFromQuote($quote, $scopeId);
            $liveMode = $this->dataHelper->isLiveMode($scopeId);
            $customerEmail = $this->eceHelper->getCustomerEmail($walletEmail, null, $quote, $scopeId);

            $quote = $this->eceHelper->fillMissingDataForPlaceOrder(
                $quote,
                $shippingAddress,
                $billingDetails,
                empty($checkoutEmail) ? $walletEmail : $checkoutEmail,
                $payerName,
                $payerPhone,
                $placementId,
                $checkoutQuoteBillingAddress,
                $checkoutQuoteShippingAddress,
                $checkoutShippingMethod,
                $scopeId
            );

            $this->checkoutSession->clearHelperData();
            $this->checkoutSession
                ->setLastQuoteId($quote->getId())
                ->setLastSuccessQuoteId($quote->getId());

            $accountId = $this->dataHelper->getAccountId(
                $scopeId,
                $liveMode
            );

            $this->eceHelper->addValidationHashToQuote($quote);
            $this->eventManager->dispatch('checkout_submit_before', ['quote' => $quote]);
            $order = $this->quoteManagement->submit($quote);
            $this->logger->logOrderEvent(
                $order,
                'Order placed with ' . \Ebizmarts\BrippoPayments\Model\ExpressCheckoutElement::METHOD_CODE . '.'
            );
            $order->setStatus(BrippoOrder::STATUS_PENDING)->save();

            try {
                $this->eventManager->dispatch('checkout_submit_all_after', ['order' => $order, 'quote' => $quote]);
            } catch (Exception $ex) {
                $this->eventManager->dispatch('brippo_checkout_submit_all_after_failed',
                    [
                        'error' => $ex,
                        'order' => $order,
                        'quote' => $quote
                    ]
                );
                $this->logger->logOrderEvent(
                    $order,
                    $ex->getMessage() . ' ' . $ex->getTraceAsString()
                );
            }

            $this->addPickedUpInputValues($order, $pickupInputValues);

            try {
                $this->logger->logOrderEvent(
                    $order,
                    'Creating payment intent...'
                );

                $paymentIntent = $this->brippoApiPaymentIntents->create(
                    $this->eceHelper->getPlaceOrderAmount($order, $scopeId),
                    $currency,
                    $this->stripeHelper->getCaptureMethod(
                        $this->dataHelper->getStoreConfig(
                            \Ebizmarts\BrippoPayments\Model\ExpressCheckoutElement::XML_PATH_CAPTURE_METHOD,
                            $scopeId
                        )
                    ),
                    $accountId,
                    $this->connectedAccountsHelper->getCountry($accountId, $scopeId),
                    $this->paymentsHelper->getMetadataForPaymentIntent(
                        $accountId,
                        $customerEmail,
                        \Ebizmarts\BrippoPayments\Model\ExpressCheckoutElement::METHOD_CODE,
                        $order->getIncrementId(),
                        $provider,
                        $quote->getEntityId(),
                        $placementId
                    ),
                    $this->paymentsHelper->getPaymentIntentDescription($order->getIncrementId()),
                    $liveMode,
                    $card,
                    $order->getIncrementId(),
                    "",
                    "",
                    $this->dataHelper->getStoreConfig(
                        DataHelper::XML_PATH_STATEMENT_DESCRIPTOR_SUFFIX,
                        $scopeId
                    )
                );
                $this->logger->logOrderEvent(
                    $order,
                    'Created payment intent ' . $paymentIntent[Stripe::PARAM_ID] . '.'
                );
            } catch (Exception $ex) {
                $this->paymentsHelper->cancelOrder(
                    $order,
                    'Order canceled as payment failed with error: ' . $ex->getMessage(),
                    $this->eceHelper->getCancelStatusFromBrippoApiError($ex)
                );
                $this->paymentsHelper->restoreQuote($order);

                throw $ex;
            }

            try {
                $this->paymentsHelper->saveCardAdditionalData(
                    $order->getPayment(),
                    $card,
                    $provider
                );
                $this->paymentsHelper->savePaymentAdditionalData(
                    $order->getPayment(),
                    $liveMode,
                    $paymentIntent[Stripe::PARAM_ID],
                    $this->eceHelper->getFrontendSourceBeautified($placementId),
                    $paymentIntent[Stripe::PARAM_STATUS],
                    $currency
                );
            } catch (Exception $ex) {
                $this->logger->logOrderEvent(
                    $order,
                    $ex->getMessage() . ' ' . $ex->getTraceAsString()
                );
            }

            $response = [
                'valid' => 1,
                'client_secret' => $paymentIntent[Stripe::PARAM_CLIENT_SECRET],
                'payment_intent_id' => $paymentIntent[Stripe::PARAM_ID],
                'order_id' => $order->getEntityId(),
                'order_increment_id' => $order->getIncrementId()
            ];
        } catch (Exception $ex) {
            $error = $this->eceHelper->prettifyErrorMessage(
                $ex->getMessage(),
                $billingDetails??[],
                $shippingAddress??[]
            );
            // phpcs:disable
            $this->logger->log(print_r($shippingAddress??[], true));
            $this->logger->log(print_r($billingDetails??[], true));
            // phpcs:enable

            if (!empty($order)) {
                $this->logger->logOrderEvent(
                    $order,
                    $ex->getMessage()
                );
            } else {
                $this->logger->log($ex->getMessage());
            }

            $response = [
                'valid' => 0,
                'message' => $error
            ];
        }

        $resultJson = $this->jsonFactory->create();
        $resultJson->setData($response);
        return $resultJson;
    }

    /**
     * @param $order
     * @param $pickupInputValues
     * @return void
     */
    private function addPickedUpInputValues($order, $pickupInputValues): void
    {
        try {
            if (!empty($pickupInputValues) && is_array($pickupInputValues)) {
                $this->logger->log('Picked up input values:');
                $this->logger->log(print_r($pickupInputValues, true));

                foreach ($order->getAllItems() as $orderItem) {
                    $opts = $orderItem->getProductOptions();
                    if (!isset($opts['attributes_info']) || !is_array($opts['attributes_info'])) {
                        $opts['attributes_info'] = [];
                    }
                    foreach ($pickupInputValues as $inputValue) {
                        $opts['attributes_info'][] = [
                            'label' => $inputValue['name'],
                            'value' => $inputValue['value']
                        ];
                    }
                    $orderItem->setProductOptions($opts);
                }
            }
        } catch (Exception $ex) {
            $this->logger->log('Unable to add picked up values:');
            $this->logger->log($ex->getMessage());
        }
    }
}
