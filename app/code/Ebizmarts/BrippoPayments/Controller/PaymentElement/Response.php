<?php

namespace Ebizmarts\BrippoPayments\Controller\PaymentElement;

use Ebizmarts\BrippoPayments\Helper\SoftFailRecover;
use Ebizmarts\BrippoPayments\Helper\BrippoApi\PaymentIntents as BrippoPaymentIntentsApi;
use Ebizmarts\BrippoPayments\Helper\Stripe;
use Ebizmarts\BrippoPayments\Model\BrippoOrder;
use Ebizmarts\BrippoPayments\Model\PaymentElement;
use Ebizmarts\BrippoPayments\Model\PaymentMethods\PaymentMethod;
use Exception;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Ebizmarts\BrippoPayments\Helper\Logger;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Store\Model\StoreManagerInterface;
use Ebizmarts\BrippoPayments\Helper\Payments as PaymentsHelper;
use Ebizmarts\BrippoPayments\Helper\Data as DataHelper;
use Ebizmarts\BrippoPayments\Plugin\CsrfFilter;
use Magento\Framework\Serialize\Serializer\Json;
use Ebizmarts\BrippoPayments\Helper\PaymentElement as PaymentElementHelper;

class Response extends Action
{
    protected $logger;
    protected $checkoutSession;
    protected $storeManager;
    protected $paymentsHelper;
    protected $dataHelper;
    protected $cookieManager;
    protected $cookieMetadataFactory;
    protected $json;
    protected $brippoApiPaymentIntents;
    protected $orderRepository;
    protected $orderSender;
    protected $paymentElementHelper;
    protected $softFailRecoverHelper;

    /**
     * @param Context $context
     * @param Logger $logger
     * @param CheckoutSession $checkoutSession
     * @param StoreManagerInterface $storeManager
     * @param PaymentsHelper $paymentsHelper
     * @param DataHelper $dataHelper
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param Json $json
     * @param BrippoPaymentIntentsApi $brippoApiPaymentIntents
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderSender $orderSender
     * @param CsrfFilter $csrfFilter
     * @param PaymentElementHelper $paymentElementHelper
     * @param SoftFailRecover $softFailRecoverHelper
     */
    public function __construct(
        Context                  $context,
        Logger                   $logger,
        CheckoutSession          $checkoutSession,
        StoreManagerInterface    $storeManager,
        PaymentsHelper           $paymentsHelper,
        DataHelper               $dataHelper,
        CookieManagerInterface   $cookieManager,
        CookieMetadataFactory    $cookieMetadataFactory,
        Json                     $json,
        BrippoPaymentIntentsApi  $brippoApiPaymentIntents,
        OrderRepositoryInterface $orderRepository,
        OrderSender              $orderSender,
        CsrfFilter               $csrfFilter,
        PaymentElementHelper     $paymentElementHelper,
        SoftFailRecover $softFailRecoverHelper
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->checkoutSession = $checkoutSession;
        $this->storeManager = $storeManager;
        $this->paymentsHelper = $paymentsHelper;
        $this->dataHelper = $dataHelper;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->json = $json;
        $this->brippoApiPaymentIntents = $brippoApiPaymentIntents;
        $this->orderRepository = $orderRepository;
        $this->orderSender = $orderSender;
        $csrfFilter->filterCrsfInterfaceImplementation($this);
        $this->paymentElementHelper = $paymentElementHelper;
        $this->softFailRecoverHelper = $softFailRecoverHelper;
    }

    /**
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        try {
            $this->paymentElementHelper->setParamsFromRequestBody($this->getRequest());
            $scopeId = $this->storeManager->getStore()->getId();
            $paymentIntentId = $this->getRequest()->getParam("payment_intent");
            $liveMode = $this->dataHelper->isLiveMode($scopeId);
            $orderId = $this->getRequest()->getParam("orderId");
            $isOrderRecover = $this->getRequest()->getParam("isOrderRecover") === 'true';

            /*
             * EMPTY PARAMS FAILSAFE
             */
            if (empty($orderId)) {
                $this->logger->log('Order ID is empty, trying to recover from checkout session...');
                $orderId = $this->checkoutSession->getLastOrderId();
                $order = $this->orderRepository->get($orderId);
                if (!empty($order) && !empty($order->getPayment())) {
                    $paymentIntentId = $order->getPayment()->getAdditionalInformation(
                        PaymentMethod::ADDITIONAL_DATA_PAYMENT_INTENT_ID
                    );
                    $this->logger->log('Recovered order #' . $order->getIncrementId() . ' with payment intent '
                        . $paymentIntentId . ' from checkout session.');
                }
            } else {
                $order = $this->orderRepository->get($orderId);
            }

            if (empty($paymentIntentId)) {
                throw new LocalizedException(__('Invalid payment intent provided.'));
            }

            $this->logger->log('Trying to complete Payment Element for Order ' . $orderId .
                ' with Payment Intent ' . $paymentIntentId . '...');

            if ($order && !empty($order->getEntityId())) {
                if (!$order->hasInvoices()) {
                    $this->logger->logOrderEvent(
                        $order,
                        'Trying to complete order...');

                    $paymentIntent = $this->brippoApiPaymentIntents->get($paymentIntentId, $liveMode);
                    $paymentIntentStatus = $paymentIntent[Stripe::PARAM_STATUS];

                    if ($paymentIntentStatus === Stripe::PAYMENT_INTENT_STATUS_SUCCEEDED) {
                        if ($isOrderRecover
                            || $order->getState() == Order::STATE_CANCELED) {
                            $this->paymentsHelper->recoverOrder($order, PaymentElement::METHOD_CODE, $isOrderRecover);
                        }
                        $this->paymentsHelper->invoiceOrder($order, $paymentIntentId);
                    } elseif ($paymentIntentStatus == Stripe::PAYMENT_INTENT_STATUS_REQUIRES_CAPTURE) {
                        if ($isOrderRecover
                            || $order->getState() == Order::STATE_CANCELED) {
                            $this->paymentsHelper->recoverOrder($order, PaymentElement::METHOD_CODE, $isOrderRecover);
                        }
                        $this->paymentsHelper->processUncapturedPaymentOrder($order);
                    } else {
                        if ($this->dataHelper->getStoreConfig(
                                SoftFailRecover::CONFIG_PATH_SOFT_FAIL_RECOVERY,
                                $scopeId
                            ) && $this->paymentsHelper->isFailedChargeAllowedForRecovery($paymentIntent[Stripe::PARAM_LATEST_CHARGE])
                        ) {
                            $order->setState(Order::STATE_HOLDED)
                                ->setStatus(BrippoOrder::STATUS_TRYING_TO_RECOVER)
                                ->save();
                            $this->softFailRecoverHelper->sendNotification(
                                $order,
                                $order->getCustomerEmail(),
                                0
                            );
                        } else {
                            if (!$isOrderRecover) {
                                $this->paymentsHelper->cancelOrder(
                                    $order,
                                    'Trying to complete order but payment status is ' . $paymentIntentStatus,
                                    BrippoOrder::STATUS_PAYMENT_FAILED
                                );
                                $this->paymentsHelper->restoreQuote($order);
                            }
                            throw new LocalizedException(__('Invalid payment status: ' . $paymentIntentStatus
                                . '. Please try again.'));
                        }
                    }

                    $this->checkoutSession
                        ->setLastOrderId($order->getId())
                        ->setLastRealOrderId($order->getIncrementId())
                        ->setLastSuccessQuoteId($order->getQuoteId())
                        ->setLastQuoteId($order->getQuoteId())
                        ->setLastOrderStatus($order->getStatus());

                    if (!$isOrderRecover) {
                        $this->clearCartCookie();
                    }

                    $this->logger->logOrderEvent(
                        $order,
                        'Order #' . $order->getIncrementId() . ' completed successfully.');
                } else {
                    $this->logger->logOrderEvent(
                        $order,
                        'Already invoiced, redirecting to success...');
                }
            } else {
                throw new LocalizedException(__('Unable to find order with id ' . $orderId));
            }

            return $this->resultRedirectFactory->create()->setPath(
                'checkout/onepage/success'
            );
        } catch (Exception $ex) {
            if (!empty($order)) {
                $this->logger->logOrderEvent(
                    $order,
                    $ex->getMessage()
                );
            } else {
                $this->logger->log($ex->getMessage());
            }

            $this->messageManager->addErrorMessage($ex->getMessage());
            return $this->resultRedirectFactory->create()->setPath(
                'checkout/cart'
            );
        }
    }

    /**
     * @return void
     */
    private function clearCartCookie()
    {
        try {
            $metadata = $this->cookieMetadataFactory
                ->createPublicCookieMetadata()
                ->setPath('/');
            $sectiondata = $this->json->unserialize($this->cookieManager->getCookie('section_data_ids'));
            $sectiondata['cart'] = isset($sectiondata['cart']) ? $sectiondata['cart'] + 1000 : 1000;
            $this->cookieManager->setPublicCookie(
                'section_data_ids',
                $this->json->serialize($sectiondata),
                $metadata
            );
        } catch (Exception $ex) {
            $this->logger->log('Unable to clear cart cookie.');
        }
    }

    protected function _isAllowed(): bool
    {
        return true;
    }

    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
