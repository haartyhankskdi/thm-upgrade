<?php

namespace Ebizmarts\BrippoPayments\Controller\PaymentElement;

use Ebizmarts\BrippoPayments\Helper\BrippoApi\PaymentIntents as BrippoPaymentIntentsApi;
use Ebizmarts\BrippoPayments\Helper\BrippoApi\Service;
use Ebizmarts\BrippoPayments\Helper\ConnectedAccounts;
use Ebizmarts\BrippoPayments\Helper\Data as DataHelper;
use Ebizmarts\BrippoPayments\Helper\Logger;
use Ebizmarts\BrippoPayments\Helper\PaymentElement as PaymentElementHelper;
use Ebizmarts\BrippoPayments\Helper\Payments as PaymentsHelper;
use Ebizmarts\BrippoPayments\Helper\RecoverCheckout;
use Ebizmarts\BrippoPayments\Helper\Stock;
use Ebizmarts\BrippoPayments\Helper\Stripe;
use Ebizmarts\BrippoPayments\Model\PaymentElement;
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
    protected $paymentElementHelper;
    protected $connectedAccountsHelper;

    /** @var OrderRepositoryInterface */
    protected $orderRepository;

    /** @var Stock */
    protected $stockHelper;

    /** @var Stripe */
    protected $stripeHelper;

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
     * @param PaymentElementHelper $paymentElementHelper
     * @param ConnectedAccounts $connectedAccountsHelper
     * @param OrderRepositoryInterface $orderRepository
     * @param Stock $stockHelper
     * @param Stripe $stripeHelper
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
        PaymentElementHelper        $paymentElementHelper,
        ConnectedAccounts           $connectedAccountsHelper,
        OrderRepositoryInterface    $orderRepository,
        Stock                       $stockHelper,
        Stripe                      $stripeHelper,
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
        $this->paymentElementHelper = $paymentElementHelper;
        $this->connectedAccountsHelper = $connectedAccountsHelper;
        $this->orderRepository = $orderRepository;
        $this->stockHelper = $stockHelper;
        $this->stripeHelper = $stripeHelper;
        $this->recoverCheckoutHelper = $recoverCheckoutHelper;
    }

    public function execute()
    {
        try {
            $this->logger->log('Trying to recover order with Payment Element...');
            $this->paymentElementHelper->setParamsFromRequestBody($this->getRequest());
            $scopeId = $this->storeManager->getStore()->getId();
            $paymentMethod = $this->getRequest()->getParam('paymentMethod');
            $recoverOrderId = $this->getRequest()->getParam('recoverOrderId');
            $linkSource = $this->getRequest()->getParam('linkSource');
            $isManual = $this->getRequest()->getParam('isManual');
            $isSoftFailRecovery = $this->getRequest()->getParam('isSoftFailRecovery');
            $notificationNumber = $this->getRequest()->getParam('notificationNumber');

            $order = $this->orderRepository->get($recoverOrderId);
            if (empty($order) || empty($order->getEntityId())) {
                throw new LocalizedException(__('Order not found'));
            }

            $this->logger->logOrderEvent(
                $order,
                'Trying to recover...'
            );

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

            $walletProvider = $this->stripeHelper->getWalletNameFromPaymentMethod($paymentMethod);
            $card = $this->stripeHelper->getCardFromPaymentMethod($paymentMethod);
            $currency = $this->paymentElementHelper->getPaymentIntentCurrencyFromOrder($order, $scopeId);
            $customerEmail = $order->getCustomerEmail();
            $liveMode = $this->dataHelper->isLiveMode($scopeId);
            $accountId = $this->dataHelper->getAccountId(
                $scopeId,
                $liveMode
            );

            $this->logger->logOrderEvent(
                $order,
                'Creating payment intent...'
            );

            $paymentIntentData = $this->brippoApiPaymentIntents->create(
                $this->paymentElementHelper->getPlaceOrderAmount($order, $scopeId),
                $currency,
                $this->paymentElementHelper->getCaptureMethod($scopeId, false),
                $accountId,
                $this->connectedAccountsHelper->getCountry($accountId, $scopeId),
                array_merge(
                    $this->paymentsHelper->getMetadataForPaymentIntent(
                        $accountId,
                        $customerEmail,
                        PaymentElement::METHOD_CODE,
                        $order->getIncrementId(),
                        $walletProvider,
                        $order->getQuoteId(),
                        'recover_checkout'
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
                isset($paymentMethod['type']) ? $paymentMethod['type'] : "",
                $this->paymentElementHelper->getThreeDSecure($scopeId, $order),
                $this->dataHelper->getStoreConfig(
                    DataHelper::XML_PATH_STATEMENT_DESCRIPTOR_SUFFIX,
                    $scopeId
                )
            );

            $this->logger->logOrderEvent(
                $order,
                'Payment Intent ' . $paymentIntentData[Service::PARAM_PI_ID] .
                ' created successfully.'
            );

            try {
                $this->stockHelper->updateStock($order);
                $this->paymentsHelper->resetPaymentDetails($order->getPayment());
                $this->paymentsHelper->saveCardAdditionalData(
                    $order->getPayment(),
                    $card,
                    $walletProvider
                );
                $this->paymentsHelper->savePaymentAdditionalData(
                    $order->getPayment(),
                    $liveMode,
                    $paymentIntentData[Service::PARAM_PI_ID],
                    'Brippo Recover Checkout',
                    $paymentIntentData[Service::PARAM_PI_STATUS],
                    $currency
                );
            } catch (Exception $ex) {
                $this->logger->log($ex->getMessage());
                $this->logger->logOrderEvent(
                    $order,
                    $ex->getMessage()
                );
            }

            $response = [
                'valid' => 1,
                'client_secret' => $paymentIntentData[Service::PARAM_PI_CLIENT_SECRET],
                'payment_intent_id' => $paymentIntentData[Service::PARAM_PI_ID],
                'order_id' => $order->getEntityId(),
                'order_increment_id' => $order->getIncrementId()
            ];

            $this->checkoutSession->clearHelperData();
            $this->checkoutSession
                ->setLastQuoteId($order->getQuoteId())
                ->setLastSuccessQuoteId($order->getQuoteId())
                ->setLastOrderId($order->getId())
                ->setLastRealOrderId($order->getIncrementId())
                ->setLastOrderStatus($order->getStatus());
        } catch (Exception $ex) {
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
                'message' => $ex->getMessage()
            ];
        }

        $resultJson = $this->jsonFactory->create();
        $resultJson->setData($response);
        return $resultJson;
    }
}
