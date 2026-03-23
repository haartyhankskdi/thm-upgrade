<?php

namespace Ebizmarts\BrippoPayments\Controller\PaymentElement;

use Ebizmarts\BrippoPayments\Helper\Data as DataHelper;
use Ebizmarts\BrippoPayments\Helper\Stripe;
use Ebizmarts\BrippoPayments\Model\Config\Source\PaymentMethodsAvailable;
use Ebizmarts\BrippoPayments\Model\PaymentElement;
use Ebizmarts\BrippoPayments\Model\PaymentMethods\PaymentElementStandalone;
use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Ebizmarts\BrippoPayments\Helper\Logger;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Ebizmarts\BrippoPayments\Helper\PaymentElement as PaymentElementHelper;

class PaymentRequest extends Action
{
    protected $jsonFactory;
    protected $logger;
    protected $checkoutSession;
    protected $storeManager;
    protected $dataHelper;
    protected $stripeHelper;
    protected $paymentElementHelper;
    protected $orderRepository;

    /**
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param Logger $logger
     * @param CheckoutSession $checkoutSession
     * @param StoreManagerInterface $storeManager
     * @param DataHelper $dataHelper
     * @param Stripe $stripeHelper
     * @param PaymentElementHelper $paymentElementHelper
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        Context                             $context,
        JsonFactory                         $jsonFactory,
        Logger                              $logger,
        CheckoutSession                     $checkoutSession,
        StoreManagerInterface               $storeManager,
        DataHelper                          $dataHelper,
        Stripe                              $stripeHelper,
        PaymentElementHelper                $paymentElementHelper,
        OrderRepositoryInterface            $orderRepository
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->logger = $logger;
        $this->checkoutSession = $checkoutSession;
        $this->storeManager = $storeManager;
        $this->dataHelper = $dataHelper;
        $this->stripeHelper = $stripeHelper;
        $this->paymentElementHelper = $paymentElementHelper;
        $this->orderRepository = $orderRepository;
    }

    public function execute()
    {
        try {
            $this->paymentElementHelper->setParamsFromRequestBody($this->getRequest());
            $scopeId = $this->storeManager->getStore()->getId();
            $placementId = $this->getRequest()->getParam('placementId');
            $recoverOrderId = $this->getRequest()->getParam('recoverOrderId');

            if ($placementId === PaymentElementHelper::PLACEMENT_ID_RECOVER_CHECKOUT) {
                $order = $this->orderRepository->get($recoverOrderId);
                if (empty($order) || empty($order->getEntityId())) {
                    throw new LocalizedException(__('Order not found'));
                }
                $amount = $order->getGrandTotal();
                $currency = $this->paymentElementHelper->getPaymentIntentCurrencyFromOrder($order, $scopeId);
                $customerEmail = $order->getCustomerEmail();
            } else {
                $quote = $this->checkoutSession->getQuote();
                $amount = $this->paymentElementHelper->getQuoteGrandTotal($quote, $scopeId);
                $currency = $this->paymentElementHelper->getPaymentIntentCurrencyFromQuote($quote, $scopeId);
                $customerEmail = $quote->getCustomerEmail();
                $this->paymentElementHelper->generateOrderUniqId($this->checkoutSession);
            }

            $response = [
                'valid' => 1,
                'options' => [
                    'mode' => 'payment',
                    'amount' => $this->stripeHelper->convertMagentoAmountToStripeAmount($amount, $currency),
                    'currency' => strtolower($currency),
                    'on_behalf_of' => $this->dataHelper->getAccountId(
                        $scopeId,
                        $this->dataHelper->isLiveMode($scopeId)
                    ),
                    'appearance' => [
                        'theme' => $this->dataHelper->getStoreConfig(
                            PaymentElement::XML_PATH_THEME,
                            $scopeId
                        ),
                        'labels' => $this->dataHelper->getStoreConfig(
                            PaymentElement::XML_PATH_LABELS,
                            $scopeId
                        ) ?? ''
                    ],
                    'paymentMethodCreation' => 'manual'
                ],
                'customerEmail' => $customerEmail
            ];

            /*
             * PAYMENT METHODS
             */
            if ($placementId === PaymentElementHelper::PLACEMENT_ID_CHECKOUT_STANDALONE) {
                $response['options']['paymentMethodTypes'] = [(string)$this->dataHelper->getStoreConfig(
                    PaymentElementStandalone::CONFIG_PAYMENT_METHOD,
                    $scopeId
                )];
            } else {
                if ($this->dataHelper->getStoreConfig(
                        PaymentElement::CONFIG_PAYMENT_METHODS_AVAILABLE,
                        $scopeId
                    ) === PaymentMethodsAvailable::MANUAL) {
                    $paymentMethodsTypes = $this->dataHelper->getStoreConfig(
                        PaymentElement::CONFIG_PAYMENT_METHODS,
                        $scopeId
                    );
                    $response['options']['paymentMethodTypes'] = !empty($paymentMethodsTypes) ? explode(',', (string)$paymentMethodsTypes) : [];
                }
            }

            $this->checkoutSession->setBrippoRequestedAmount($amount);
        } catch (Exception $ex) {
            $this->logger->log($ex->getMessage());
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
