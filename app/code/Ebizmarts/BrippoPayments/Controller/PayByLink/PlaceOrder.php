<?php

namespace Ebizmarts\BrippoPayments\Controller\PayByLink;

use Ebizmarts\BrippoPayments\Helper\BrippoApi\PaymentLinks as BrippoPaymentLinksApi;
use Ebizmarts\BrippoPayments\Helper\BrippoApi\Service;
use Ebizmarts\BrippoPayments\Helper\Data as DataHelper;
use Ebizmarts\BrippoPayments\Helper\Logger;
use Ebizmarts\BrippoPayments\Helper\PayByLink as PayByLinkHelper;
use Ebizmarts\BrippoPayments\Helper\Payments as PaymentsHelper;
use Ebizmarts\BrippoPayments\Helper\Stripe;
use Ebizmarts\BrippoPayments\Model\BrippoOrder;
use Ebizmarts\BrippoPayments\Model\PayByLink;
use Ebizmarts\BrippoPayments\Model\PaymentMethods\PaymentMethod;
use Exception;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;

class PlaceOrder extends Action
{
    protected $jsonFactory;
    protected $logger;
    protected $brippoApiPaymentLinks;
    protected $dataHelper;
    protected $checkoutSession;
    protected $storeManager;
    protected $customerSession;
    protected $quoteManagement;
    protected $paymentsHelper;
    protected $payByLinkHelper;
    protected $orderRepository;

    /**
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param Logger $logger
     * @param BrippoPaymentLinksApi $brippoApiPaymentLinks
     * @param DataHelper $dataHelper
     * @param CheckoutSession $checkoutSession
     * @param StoreManagerInterface $storeManager
     * @param CustomerSession $customerSession
     * @param CartManagementInterface $quoteManagement
     * @param PaymentsHelper $paymentsHelper
     * @param PayByLinkHelper $payByLinkHelper
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        Context                 $context,
        JsonFactory             $jsonFactory,
        Logger                  $logger,
        BrippoPaymentLinksApi   $brippoApiPaymentLinks,
        DataHelper              $dataHelper,
        CheckoutSession         $checkoutSession,
        StoreManagerInterface   $storeManager,
        CustomerSession         $customerSession,
        CartManagementInterface $quoteManagement,
        PaymentsHelper          $paymentsHelper,
        PayByLinkHelper         $payByLinkHelper,
        OrderRepositoryInterface $orderRepository
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->logger = $logger;
        $this->brippoApiPaymentLinks = $brippoApiPaymentLinks;
        $this->dataHelper = $dataHelper;
        $this->checkoutSession = $checkoutSession;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->quoteManagement = $quoteManagement;
        $this->paymentsHelper = $paymentsHelper;
        $this->payByLinkHelper = $payByLinkHelper;
        $this->orderRepository = $orderRepository;
    }

    public function execute()
    {
        $this->logger->log('Trying to place order from Pay by Link...');
        $scopeId = $this->storeManager->getStore()->getId();
        $billingAddress = $this->getRequest()->getParam('billingAddress');
        $shippingAddress = $this->getRequest()->getParam('shippingAddress');

        try {
            $quote = $this->checkoutSession->getQuote();
            $currency = $this->payByLinkHelper->getPaymentIntentCurrencyFromQuote($quote, $scopeId);

            /*
             * Fill possibly missing quote data
             */
            if (!$this->payByLinkHelper->isAddressValidForOrderSubmit($quote->getBillingAddress())
                && !empty($billingAddress)) {
                $this->payByLinkHelper->setBillingAddressFromFrontendData($quote, $billingAddress);
            }
            if (!$quote->isVirtual()
                && !$this->payByLinkHelper->isAddressValidForOrderSubmit($quote->getShippingAddress())
                && !empty($shippingAddress)) {
                $this->payByLinkHelper->setShippingAddressFromFrontendData($quote, $shippingAddress);
            }
            $customerEmail = $quote->getCustomerEmail();
            if (!$this->customerSession->isLoggedIn()) {
                $quote->setCustomerIsGuest(true);
                if (filter_var($quote->getBillingAddress()->getEmail(), FILTER_VALIDATE_EMAIL)) {
                    $quote->setCustomerEmail($quote->getBillingAddress()->getEmail());
                    $customerEmail = $quote->getBillingAddress()->getEmail();
                } else {
                    $quote->setCustomerEmail('guest@example.com');
                }
            }

            $this->payByLinkHelper->fillMissingDataForPlaceOrder($quote);
            $liveMode = $this->dataHelper->isLiveMode($scopeId);

            $this->checkoutSession->clearHelperData();
            $this->checkoutSession
                ->setLastQuoteId($quote->getId())
                ->setLastSuccessQuoteId($quote->getId());

            $accountId = $this->dataHelper->getAccountId(
                $scopeId,
                $liveMode
            );

            $this->payByLinkHelper->addValidationHashToQuote($quote);
            $this->payByLinkHelper->generateOrderUniqId($this->checkoutSession);

            $order = $this->quoteManagement->submit($quote);
            $this->logger->logOrderEvent(
                $order,
                'Order placed with ' . PayByLink::METHOD_CODE . '.'
            );

            $order->setState(Order::STATE_PENDING_PAYMENT);
            $order->setStatus(BrippoOrder::STATUS_PENDING);
            $this->orderRepository->save($order);

            $paymentLink = $this->brippoApiPaymentLinks->create(
                $this->payByLinkHelper->getPlaceOrderAmount($order, $scopeId),
                $currency,
                $order->getIncrementId(),
                $accountId,
                PayByLink::KEY_HOSTED_CONFIRMATION,
                $this->dataHelper->getStoreConfig(
                    PayByLink::XML_PATH_STORE_CONFIG_HOSTED_CONFIRM_MSG,
                    $scopeId
                ),
                '',
                $this->paymentsHelper->getMetadataForPaymentIntent(
                    $accountId,
                    $customerEmail,
                    PayByLink::METHOD_CODE,
                    $order->getIncrementId(),
                    '',
                    $quote->getEntityId(),
                    'checkout'
                ),
                $liveMode
            );

            $this->logger->logOrderEvent(
                $order,
                'Payment Link created with ID ' .
                    $paymentLink[Service::PARAM_PL_ID] . '.'
            );

            $emailSent = $this->payByLinkHelper->sendFinalizePaymentEmail(
                $scopeId,
                $paymentLink[Stripe::PARAM_URL],
                $order,
                $customerEmail
            );

            $order->getPayment()->setAdditionalInformation(
                PaymentMethod::ADDITIONAL_DATA_PAYMENT_LINK_ID,
                $paymentLink[Service::PARAM_PL_ID]
            )->setAdditionalInformation(
                PaymentMethod::ADDITIONAL_DATA_LIVEMODE,
                $liveMode
            )->setAdditionalInformation(
                PaymentMethod::ADDITIONAL_DATA_PAYMENT_LINK_URL,
                $paymentLink[Stripe::PARAM_URL]
            )->setAdditionalInformation(
                PaymentMethod::ADDITIONAL_DATA_WAS_EMAIL_SENT,
                $emailSent
            )->save();

            $this->checkoutSession
                ->setLastOrderId($order->getId())
                ->setLastRealOrderId($order->getIncrementId())
                ->setLastSuccessQuoteId($order->getQuoteId())
                ->setLastQuoteId($order->getQuoteId())
                ->setLastOrderStatus($order->getStatus());

            $response = [
                'valid' => 1,
                'payment_link_url' => $paymentLink[Stripe::PARAM_URL],
                'order_id' => $order->getEntityId(),
                'order_increment_id' => $order->getIncrementId(),
                'email_sent' => $emailSent
            ];

            $this->logger->logOrderEvent(
                $order,
                'Pay by Link process completed successfully. Awaiting payment...'
            );
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
