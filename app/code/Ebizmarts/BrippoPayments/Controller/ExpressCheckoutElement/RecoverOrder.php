<?php

namespace Ebizmarts\BrippoPayments\Controller\ExpressCheckoutElement;

use Ebizmarts\BrippoPayments\Helper\BrippoApi\PaymentIntents as BrippoPaymentIntentsApi;
use Ebizmarts\BrippoPayments\Helper\BrippoApi\Service;
use Ebizmarts\BrippoPayments\Helper\ConnectedAccounts;
use Ebizmarts\BrippoPayments\Helper\Data as DataHelper;
use Ebizmarts\BrippoPayments\Helper\ExpressCheckoutElement;
use Ebizmarts\BrippoPayments\Helper\Logger;
use Ebizmarts\BrippoPayments\Helper\Payments as PaymentsHelper;
use Ebizmarts\BrippoPayments\Helper\RecoverCheckout;
use Ebizmarts\BrippoPayments\Helper\Stock;
use Ebizmarts\BrippoPayments\Helper\Stripe;
use Ebizmarts\BrippoPayments\Model\Config\Source\ExpressLocation;
use Ebizmarts\BrippoPayments\Model\PaymentMethods\PaymentMethod;
use Exception;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

class RecoverOrder extends Action
{
    protected $jsonFactory;
    protected $logger;
    protected $brippoApiPaymentIntents;
    protected $dataHelper;
    protected $checkoutSession;
    protected $storeManager;
    protected $paymentsHelper;
    protected $eceHelper;
    protected $connectedAccountsHelper;

    /** @var OrderRepositoryInterface */
    protected $orderRepository;

    /** @var Stripe */
    protected $stripeHelper;

    /** @var Stock */
    protected $stockHelper;

    protected $recoverCheckoutHelper;

    /**
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param Logger $logger
     * @param BrippoPaymentIntentsApi $brippoApiPaymentIntents
     * @param DataHelper $dataHelper
     * @param CheckoutSession $checkoutSession
     * @param StoreManagerInterface $storeManager
     * @param PaymentsHelper $paymentsHelper
     * @param ExpressCheckoutElement $eceHelper
     * @param ConnectedAccounts $connectedAccountsHelper
     * @param OrderRepositoryInterface $orderRepository
     * @param Stripe $stripeHelper
     * @param Stock $stockHelper
     * @param RecoverCheckout $recoverCheckoutHelper
     */
    public function __construct(
        Context                     $context,
        JsonFactory                 $jsonFactory,
        Logger                      $logger,
        BrippoPaymentIntentsApi     $brippoApiPaymentIntents,
        DataHelper                  $dataHelper,
        CheckoutSession             $checkoutSession,
        StoreManagerInterface       $storeManager,
        PaymentsHelper              $paymentsHelper,
        ExpressCheckoutElement      $eceHelper,
        ConnectedAccounts           $connectedAccountsHelper,
        OrderRepositoryInterface    $orderRepository,
        Stripe                      $stripeHelper,
        Stock                       $stockHelper,
        RecoverCheckout             $recoverCheckoutHelper
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->logger = $logger;
        $this->brippoApiPaymentIntents = $brippoApiPaymentIntents;
        $this->dataHelper = $dataHelper;
        $this->checkoutSession = $checkoutSession;
        $this->storeManager = $storeManager;
        $this->paymentsHelper = $paymentsHelper;
        $this->stripeHelper = $stripeHelper;
        $this->eceHelper = $eceHelper;
        $this->connectedAccountsHelper = $connectedAccountsHelper;
        $this->orderRepository = $orderRepository;
        $this->stockHelper = $stockHelper;
        $this->recoverCheckoutHelper = $recoverCheckoutHelper;
    }

    public function execute()
    {
        try {
            $this->eceHelper->setParamsFromRequestBody($this->getRequest());
            $scopeId = $this->storeManager->getStore()->getId();
            $paymentMethod = $this->getRequest()->getParam('paymentMethod');
            $payerName = $paymentMethod['billing_details']['name']??'UNKNOWN';
            $walletEmail = $paymentMethod['billing_details']['email']??'UNKNOWN';
            $recoverOrderId = $this->getRequest()->getParam('recoverOrderId');
            $linkSource = $this->getRequest()->getParam('linkSource');
            $isManual = $this->getRequest()->getParam('isManual');
            $isSoftFailRecovery = $this->getRequest()->getParam('isSoftFailRecovery');
            $notificationNumber = $this->getRequest()->getParam('notificationNumber');

            $order = $this->orderRepository->get($recoverOrderId);
            if (empty($order) || empty($order->getEntityId())) {
                throw new LocalizedException(__('Order not found'));
            }

            $this->logger->log('Trying to recover order # ' . $order->getIncrementId() . ' for ' . $payerName
                . ' with email address: ' . $walletEmail . '.');

            if ($notificationNumber === null
                && $order->getPayment()
                && !empty($order->getPayment()->getAdditionalInformation(
                    PaymentMethod::ADDITIONAL_DATA_RECOVER_TRIES
                ))) {
                $notificationNumber = intval($order->getPayment()->getAdditionalInformation(
                    PaymentMethod::ADDITIONAL_DATA_RECOVER_TRIES
                ));
            }

            if (empty($linkSource)) {
                $linkSource = 'link';
            }

            $provider = $this->stripeHelper->getWalletNameFromPaymentMethod($paymentMethod);
            $card = $this->stripeHelper->getCardFromPaymentMethod($paymentMethod);
            $currency = $this->eceHelper->getPaymentIntentCurrencyFromOrder($order, $scopeId);
            $customerEmail = $order->getCustomerEmail();
            $liveMode = $this->dataHelper->isLiveMode($scopeId);
            $accountId = $this->dataHelper->getAccountId(
                $scopeId,
                $liveMode
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
                array_merge(
                    $this->paymentsHelper->getMetadataForPaymentIntent(
                        $accountId,
                        $customerEmail,
                        \Ebizmarts\BrippoPayments\Model\ExpressCheckoutElement::METHOD_CODE,
                        $order->getIncrementId(),
                        $provider,
                        $order->getQuoteId(),
                        ExpressLocation::RECOVER_CHECKOUT
                    ),
                    $this->recoverCheckoutHelper->getRecoverCheckoutMetadata(
                        $linkSource,
                        $isManual,
                        $order->getPayment()->getMethod(),
                        $isSoftFailRecovery,
                        $notificationNumber
                    )
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

            $this->logger->log('Payment Intent ' . $paymentIntent[Service::PARAM_PI_ID] .
                ' created successfully for order #' . $order->getIncrementId() . '.');

            try {
                $this->stockHelper->updateStock($order);
                $this->paymentsHelper->resetPaymentDetails($order->getPayment());
                $this->paymentsHelper->saveCardAdditionalData(
                    $order->getPayment(),
                    $card,
                    $provider
                );
                $this->paymentsHelper->savePaymentAdditionalData(
                    $order->getPayment(),
                    $liveMode,
                    $paymentIntent[Stripe::PARAM_ID],
                    'Brippo Recover Checkout',
                    $paymentIntent[Stripe::PARAM_STATUS],
                    $currency
                );
            } catch (Exception $ex) {
                $this->logger->log($ex->getMessage());
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
                null,
                null
            );
            $this->logger->log($error);
            $this->logger->log($ex->getTraceAsString());
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
