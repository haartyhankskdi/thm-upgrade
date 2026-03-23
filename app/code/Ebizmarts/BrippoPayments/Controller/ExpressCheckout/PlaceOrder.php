<?php

namespace Ebizmarts\BrippoPayments\Controller\ExpressCheckout;

use Ebizmarts\BrippoPayments\Helper\ConnectedAccounts;
use Ebizmarts\BrippoPayments\Helper\Data as DataHelper;
use Ebizmarts\BrippoPayments\Helper\Payments as PaymentsHelper;
use Ebizmarts\BrippoPayments\Helper\Stripe;
use Ebizmarts\BrippoPayments\Model\BrippoOrder;
use Ebizmarts\BrippoPayments\Model\Express;
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
use Ebizmarts\BrippoPayments\Helper\Express as ExpressHelper;
use Magento\Store\Model\StoreManagerInterface;

class PlaceOrder extends Action
{
    protected $logger;
    protected $quoteManagement;
    protected $checkoutSession;
    protected $quoteFactory;
    protected $brippoApiPaymentIntents;
    protected $jsonFactory;
    protected $expressHelper;
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
     * @param ExpressHelper $expressHelper
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
        ExpressHelper           $expressHelper,
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
        $this->expressHelper = $expressHelper;
        $this->storeManager = $storeManager;
        $this->dataHelper = $dataHelper;
        $this->paymentsHelper = $paymentsHelper;
        $this->stripeHelper = $stripeHelper;
        $this->connectedAccountsHelper = $connectedAccountsHelper;
        $this->eventManager = $eventManager;
    }

    public function execute()
    {
        try {
            $this->expressHelper->setParamsFromRequestBody($this->getRequest());
            $quote = $this->checkoutSession->getQuote();
            $scopeId = $this->storeManager->getStore()->getId();
            $checkoutEmail = $this->getRequest()->getParam('checkoutEmail');
            $payerName = $this->getRequest()->getParam('payerName');
            $walletEmail = $this->getRequest()->getParam('payerEmail');
            $payerPhone = $this->getRequest()->getParam('payerPhone');
            $shippingAddress = $this->getRequest()->getParam('shippingAddress');
            $billingDetails = $this->getRequest()->getParam('billingDetails');
            $source = $this->getRequest()->getParam('source');
            $cardDetails = $this->getRequest()->getParam('card');
            $provider = $this->getRequest()->getParam('provider');
            $frontendQuoteBillingAddress = $this->getRequest()->getParam('billingAddress');
            $frontendQuoteShippingAddress = $this->getRequest()->getParam('checkoutShippingAddress');
            $frontendShippingMethod = $this->getRequest()->getParam('shippingMethod');

            $this->logger->log(
                'Trying to place order for ' .
                $this->dataHelper->hideCustomerInfo($payerName) .
                ' with email address: ' .
                $this->dataHelper->hideCustomerInfo($walletEmail) .
                '.'
            );

            if (empty($quote->getId())) {
                throw new LocalizedException(__('Your cart is empty, please refresh the page and try again.'));
            }

            $currency = $this->expressHelper->getPaymentIntentCurrencyFromQuote($quote, $scopeId);
            $liveMode = $this->dataHelper->isLiveMode($scopeId);
            $customerEmail = $this->expressHelper->getCustomerEmail($walletEmail, $checkoutEmail, $quote, $scopeId);

            $quote = $this->expressHelper->fillMissingDataForPlaceOrder(
                $quote,
                $shippingAddress,
                $billingDetails,
                $customerEmail,
                $payerName,
                $payerPhone,
                $source,
                $frontendQuoteBillingAddress,
                $frontendQuoteShippingAddress,
                $frontendShippingMethod,
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

            $this->expressHelper->addValidationHashToQuote($quote);
            $this->eventManager->dispatch('checkout_submit_before', ['quote' => $quote]);
            $order = $this->quoteManagement->submit($quote);

            $this->logger->logOrderEvent(
                $order,
                'Order placed with ' . Express::METHOD_CODE . '.'
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

            try {
                $this->logger->logOrderEvent(
                    $order,
                    'Creating payment intent...'
                );
                $paymentIntent = $this->brippoApiPaymentIntents->create(
                    $this->expressHelper->getPlaceOrderAmount($order, $scopeId),
                    $currency,
                    $this->stripeHelper->getCaptureMethod(
                        $this->dataHelper->getStoreConfig(
                            Express::XML_PATH_CAPTURE_METHOD,
                            $scopeId
                        )
                    ),
                    $accountId,
                    $this->connectedAccountsHelper->getCountry($accountId, $scopeId),
                    $this->paymentsHelper->getMetadataForPaymentIntent(
                        $accountId,
                        $customerEmail,
                        Express::METHOD_CODE,
                        $order->getIncrementId(),
                        $provider ?? 'Undefined',
                        $quote->getEntityId(),
                        $source
                    ),
                    $this->paymentsHelper->getPaymentIntentDescription($order->getIncrementId()),
                    $liveMode,
                    $cardDetails,
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
                    $this->expressHelper->getCancelStatusFromBrippoApiError($ex)
                );
                $this->paymentsHelper->restoreQuote($order);
                throw $ex;
            }

            try {
                $this->paymentsHelper->saveCardAdditionalData(
                    $order->getPayment(),
                    $cardDetails,
                    $provider
                );
                $this->paymentsHelper->savePaymentAdditionalData(
                    $order->getPayment(),
                    $liveMode,
                    $paymentIntent[Stripe::PARAM_ID],
                    $this->expressHelper->getFrontendSourceBeautified($source),
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
            $error = $this->expressHelper->prettifyErrorMessage(
                $ex->getMessage(),
                $billingDetails??[],
                $shippingAddress??[]
            );

            if (!empty($order)) {
                $this->logger->logOrderEvent(
                    $order,
                    $ex->getMessage()
                );
            } else {
                $this->logger->log($ex->getMessage());
            }

            // phpcs:disable
            $this->logger->log(print_r($shippingAddress??[], true));
            $this->logger->log(print_r($billingDetails??[], true));
            // phpcs:enable
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
