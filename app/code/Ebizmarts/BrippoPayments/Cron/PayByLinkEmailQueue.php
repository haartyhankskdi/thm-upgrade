<?php

namespace Ebizmarts\BrippoPayments\Cron;

use Ebizmarts\BrippoPayments\Helper\Logger;
use Ebizmarts\BrippoPayments\Helper\PayByLink as PayByLinkHelper;
use Ebizmarts\BrippoPayments\Model\PayByLink;
use Ebizmarts\BrippoPayments\Model\PayByLinkMoto;
use Ebizmarts\BrippoPayments\Model\PaymentMethods\PaymentMethod;
use Exception;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class PayByLinkEmailQueue
{
    const MAX_RETRY = 5;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var PayByLinkHelper
     */
    protected $payByLinkHelper;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Logger $logger
     * @param PayByLinkHelper $payByLinkHelper
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Logger $logger,
        PayByLinkHelper $payByLinkHelper
    ) {
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->logger = $logger;
        $this->payByLinkHelper = $payByLinkHelper;
    }

    /**
     * @return void
     */
    public function processQueue()
    {
        $dateFrom = date('Y-m-d H:i:s', strtotime('today -30 minutes'));
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(OrderInterface::STATUS, "pending_payment", 'eq')
            ->addFilter('created_at', $dateFrom, 'gteq')
            ->addFilter('created_at', date('Y-m-d H:i:s', strtotime("now -10 minutes")), 'lt')
            ->create();
        $orders = $this->orderRepository->getList($searchCriteria);

        $pendingOrdersFound = $orders->getItems();
        foreach ($pendingOrdersFound as $order) {
            $this->processPendingOrder($order);
        }
    }

    /**
     * @param OrderInterface $order
     * @return void
     */
    private function processPendingOrder($order)
    {
        try {
            $payment = $order->getPayment();
            if (!empty($payment) &&
                ($payment->getMethod() === PayByLink::METHOD_CODE
                    || $payment->getMethod() === PayByLinkMoto::METHOD_CODE)
            ) {
                $wasEmailSent = $payment->getAdditionalInformation(
                    PaymentMethod::ADDITIONAL_DATA_WAS_EMAIL_SENT
                );
                $emailSendTries = $payment->getAdditionalInformation(
                    PaymentMethod::ADDITIONAL_DATA_EMAIL_SEND_TRIES
                );
                $paymentLinkUrl = $payment->getAdditionalInformation(
                    PaymentMethod::ADDITIONAL_DATA_PAYMENT_LINK_URL
                );
                if (!$wasEmailSent) {
                    if (!empty($emailSendTries) && $emailSendTries >= self::MAX_RETRY) {
                        $this->logger->log('Pay by Link order #' .
                            $order->getIncrementId() . ' exceeded max retries for sending email.');
                        return;
                    }

                    $this->logger->log('Trying to send email for Pay by Link order #' .
                        $order->getIncrementId() . '...');

                    if (empty($paymentLinkUrl)) {
                        $this->logger->log('Pay by Link order #' .
                            $order->getIncrementId() . ' has no payment link URL.');
                        return;
                    }

                    $emailSent = $this->payByLinkHelper->sendFinalizePaymentEmail(
                        $order->getStoreId(),
                        $paymentLinkUrl,
                        $order,
                        $order->getCustomerEmail()
                    );

                    if (!$emailSent) {
                        if (!empty($emailSendTries)) {
                            $emailSendTries = 1;
                        } else {
                            ++$emailSendTries;
                        }
                        $payment->setAdditionalInformation(
                            PaymentMethod::ADDITIONAL_DATA_EMAIL_SEND_TRIES,
                            $emailSendTries
                        )->save();
                    } else {
                        $payment->setAdditionalInformation(
                            PaymentMethod::ADDITIONAL_DATA_WAS_EMAIL_SENT,
                            true
                        )->save();
                    }
                }
            }
        } catch (Exception $ex) {
            $this->logger->log($ex->getMessage());
        }
    }
}
