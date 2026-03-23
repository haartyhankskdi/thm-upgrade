<?php

namespace Ebizmarts\BrippoPayments\Observer;

use Ebizmarts\BrippoPayments\Helper\BrippoApi\PaymentIntents as BrippoPaymentIntentsApi;
use Ebizmarts\BrippoPayments\Helper\BrippoApi\PaymentLinks as BrippoPaymentLinksApi;
use Ebizmarts\BrippoPayments\Helper\Data as DataHelper;
use Ebizmarts\BrippoPayments\Helper\Logger;
use Ebizmarts\BrippoPayments\Helper\PaymentMethods\PaymentMethod as PaymentMethodsHelper;
use Ebizmarts\BrippoPayments\Helper\Stripe;
use Ebizmarts\BrippoPayments\Model\PaymentMethods\PaymentMethod;
use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;

class OrderCancelAfter implements ObserverInterface
{
    protected $dataHelper;
    protected $storeManager;
    protected $brippoApiPaymentIntents;
    protected $brippoApiPaymentLinks;
    protected $logger;
    protected $paymentMethodHelper;

    /**
     * @param DataHelper $dataHelper
     * @param StoreManagerInterface $storeManager
     * @param BrippoPaymentIntentsApi $brippoApiPaymentIntents
     * @param BrippoPaymentLinksApi $brippoApiPaymentLinks
     * @param Logger $logger
     * @param PaymentMethodsHelper $paymentMethodHelper
     */
    public function __construct(
        DataHelper              $dataHelper,
        StoreManagerInterface   $storeManager,
        BrippoPaymentIntentsApi $brippoApiPaymentIntents,
        BrippoPaymentLinksApi   $brippoApiPaymentLinks,
        Logger                  $logger,
        PaymentMethodsHelper    $paymentMethodHelper
    ) {
        $this->dataHelper = $dataHelper;
        $this->storeManager = $storeManager;
        $this->brippoApiPaymentIntents = $brippoApiPaymentIntents;
        $this->brippoApiPaymentLinks = $brippoApiPaymentLinks;
        $this->logger = $logger;
        $this->paymentMethodHelper = $paymentMethodHelper;
    }

    /**
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function execute(Observer $observer)
    {
        /**
         * CANCEL UNCAPTURED STRIPE PAYMENT AFTER ORDER CANCEL
         */

        try {
            /**
             * @var Order
             */
            $order = $observer->getEvent()->getOrder();
            $scopeId = $this->storeManager->getStore()->getId();

            if ($this->paymentMethodHelper->isFrontendPaymentMethod($order->getPayment()->getMethodInstance()->getCode())) {
                $lastPaymentIntentStatus = $order->getPayment()->getAdditionalInformation(
                    PaymentMethod::ADDITIONAL_DATA_STATUS
                );

                $liveMode = $order->getPayment()->getAdditionalInformation(
                    PaymentMethod::ADDITIONAL_DATA_LIVEMODE
                );
                if (empty($liveMode)) {
                    $liveMode = $this->dataHelper->isLiveMode($scopeId);
                }

                if ($order->isCanceled() &&
                    !$order->hasInvoices() &&
                    !empty($lastPaymentIntentStatus) &&
                    ($lastPaymentIntentStatus == Stripe::PAYMENT_INTENT_STATUS_REQUIRES_CAPTURE ||
                        $lastPaymentIntentStatus == Stripe::PAYMENT_INTENT_STATUS_REQUIRES_PAYMENT_METHOD)) {
                    $paymentIntentId = $order->getPayment()->getAdditionalInformation(
                        PaymentMethod::ADDITIONAL_DATA_PAYMENT_INTENT_ID
                    );
                    if (empty($paymentIntentId)) {
                        $this->logger->log('Can not find Payment Intent Id.');
                        return;
                    }
                    $paymentIntent = $this->brippoApiPaymentIntents->get($paymentIntentId, $liveMode);
                    $currentPaymentIntentStatus = $paymentIntent[Stripe::PARAM_STATUS];
                    if ($currentPaymentIntentStatus === Stripe::PAYMENT_INTENT_STATUS_REQUIRES_CAPTURE) {
                        $this->logger->log('Trying to cancel ' . $currentPaymentIntentStatus . ' payment intent...');
                        $paymentIntent = $this->brippoApiPaymentIntents->cancel(
                            $paymentIntentId,
                            $liveMode
                        );
                        $currentPaymentIntentStatus = $paymentIntent[Stripe::PARAM_STATUS];

                        $order->getPayment()->setAdditionalInformation(
                            PaymentMethod::ADDITIONAL_DATA_STATUS,
                            $currentPaymentIntentStatus
                        )->save();

                        $order->addCommentToStatusHistory(
                            __(
                                'Payment intent %1 cancelled successfully.',
                                $paymentIntentId
                            )
                        )->save();

                        $this->logger->log('Payment ' . $paymentIntentId . ' cancelled successfully with status: '
                            . $currentPaymentIntentStatus . '.');
                    }
                }
                if ($this->paymentMethodHelper->isPayByLink($order->getPayment()->getMethodInstance()->getCode())) {
                    $this->cancelPayByLink($order, $liveMode);
                }
            }
        } catch (Exception $ex) {
            $this->logger->log($ex->getMessage());
            $this->logger->log($ex->getTraceAsString());
        }
    }

    /**
     * @param Order $order
     * @param bool $liveMode
     * @return void
     */
    public function cancelPayByLink(Order $order, $liveMode){
        try {
            $paymentLinkId = $order->getPayment()->getAdditionalInformation(
                PaymentMethod::ADDITIONAL_DATA_PAYMENT_LINK_ID
            );
            $this->brippoApiPaymentLinks->cancel(
                strval($paymentLinkId),
                $liveMode
            );
        } catch (Exception $ex) {
            $this->logger->log($ex->getMessage());
            $this->logger->log($ex->getTraceAsString());
        }
    }
}
