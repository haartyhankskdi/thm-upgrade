<?php

namespace Ebizmarts\BrippoPayments\Cron;

use Ebizmarts\BrippoPayments\Helper\Payments;
use Ebizmarts\BrippoPayments\Helper\SoftFailRecover;
use Ebizmarts\BrippoPayments\Model\BrippoOrder;
use Exception;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Ebizmarts\BrippoPayments\Helper\Logger;
use Ebizmarts\BrippoPayments\Helper\Data as DataHelper;
use Magento\Sales\Model\Order;

class SoftFailRecoverCheck
{
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
     * @var DataHelper
     */
    protected $dataHelper;

    protected $paymentsHelper;

    /**
     * @var SoftFailRecover
     */
    protected $softFailHelper;

    /**
     * @var int
     */
    protected $expirationHours;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Logger $logger
     * @param DataHelper $dataHelper
     * @param Payments $paymentsHelper
     * @param SoftFailRecover $softFailHelper
     * @throws NoSuchEntityException
     */
    public function __construct(
        OrderRepositoryInterface    $orderRepository,
        SearchCriteriaBuilder       $searchCriteriaBuilder,
        Logger                      $logger,
        DataHelper                  $dataHelper,
        Payments                    $paymentsHelper,
        SoftFailRecover             $softFailHelper
    ) {
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->logger = $logger;
        $this->dataHelper = $dataHelper;
        $this->paymentsHelper = $paymentsHelper;
        $this->softFailHelper = $softFailHelper;
        $this->expirationHours = intval($this->dataHelper->getStoreConfig(SoftFailRecover::CONFIG_PATH_EXPIRATION_HOURS));
    }

    /**
     * @return void
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function checkOrders(): void
    {
        if (!$this->dataHelper->getStoreConfig(SoftFailRecover::CONFIG_PATH_SOFT_FAIL_RECOVERY)
            || !$this->dataHelper->isServiceReady()) {
            return;
        }

        $this->logger->log('Running soft failed recover check...', Logger::SOFT_FAILED_LOG);

        /*
         * CHECK EXPIRATION
         */
        $this->checkOrdersToExpire();

        /*
         * CHECK NOTIFICATIONS
         */
        $this->checkNotifications();

        $this->logger->log('################ END ################', Logger::SOFT_FAILED_LOG);
    }

    /**
     * @return void
     * @throws NoSuchEntityException
     */
    private function checkNotifications(): void
    {
        $timesToNotify = $this->dataHelper->getStoreConfig(
            SoftFailRecover::CONFIG_PATH_NOTIFICATIONS
        );
        if ($timesToNotify === 0) {
            return;
        }
        $hoursBetweenNotifications = floor($this->expirationHours / ($timesToNotify + 1));

        while ($timesToNotify > 0) {
            $creationDateExpected = $hoursBetweenNotifications * $timesToNotify;
            $dateFrom = date('Y-m-d H:i:s', strtotime('now - ' . ($creationDateExpected + 1) . ' hours'));
            $dateTo = date('Y-m-d H:i:s', strtotime('now - ' . $creationDateExpected . ' hours'));
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('status', BrippoOrder::STATUS_TRYING_TO_RECOVER)
                ->addFilter('state', Order::STATE_HOLDED)
                ->addFilter('created_at', $dateFrom, 'gteq')
                ->addFilter('created_at', $dateTo, 'lt')
                ->create();
            $ordersToNotify = $this->orderRepository->getList($searchCriteria)->getItems();

            if (count($ordersToNotify) === 0) {
                $this->logger->log(
                    'No orders are due to notify.',
                    Logger::SOFT_FAILED_LOG
                );
            } else {
                $this->logger->log(
                    'Found ' . count($ordersToNotify) . ' order' . (count($ordersToNotify) > 1 ? 's' : '')
                    . ' to send notification number ' . $timesToNotify . '...',
                    Logger::CRON_LOG
                );

                foreach ($ordersToNotify as $order) {
                    $this->sendNotification($order, $timesToNotify);
                }
            }

            $timesToNotify--;
        }
    }

    private function sendNotification($order, $notificationNumber): void
    {
        try {
            $scopeId = $order->getStoreId();
            $customerEmail = $order->getCustomerEmail();
            if (empty($customerEmail) && !empty($order->getBillingAddress()->getEmail())) {
                $customerEmail = $order->getBillingAddress()->getEmail();
            }
            if (empty($customerEmail)) {
                throw new LocalizedException(__('Customer email not found'));
            }

            /*
             * EMAIL
             */
            $this->sendEmail($order, $customerEmail, $notificationNumber);

            /*
             * SMS & WHATSAPP (NOT YET SUPPORTED)
             */
//            $customerPhone = null;
//            if (!empty($order->getBillingAddress()->getTelephone())) {
//                $customerPhone = $order->getBillingAddress()->getTelephone();
//            }
//            if (!empty($customerPhone)) {
//                $this->sendSMS($order, $customerPhone, $scopeId);
//                $this->sendWhatsApp($order, $customerPhone, $scopeId);
//            } else {
//                $this->logger->log('Customer phone not found', Logger::RECOVER_LOG);
//            }
        } catch (Exception $ex) {
            $this->logger->log($ex->getMessage(), Logger::SOFT_FAILED_LOG);
        }
    }

    private function checkOrdersToExpire():void
    {
        $dateFrom = date('Y-m-d H:i:s', strtotime('now - ' . ($this->expirationHours + 1) . ' hours'));
        $dateTo = date('Y-m-d H:i:s', strtotime('now - ' . $this->expirationHours . ' hours'));
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('status', BrippoOrder::STATUS_TRYING_TO_RECOVER)
            ->addFilter('state', Order::STATE_HOLDED)
            ->addFilter('created_at', $dateFrom, 'gteq')
            ->addFilter('created_at', $dateTo, 'lt')
            ->create();
        $ordersToExpire = $this->orderRepository->getList($searchCriteria)->getItems();

        if (count($ordersToExpire) === 0) {
            $this->logger->log(
                'No orders are due to expire.',
                Logger::SOFT_FAILED_LOG
            );
            return;
        }

        $this->logger->log(
            'Found ' . count($ordersToExpire) . ' order' . (count($ordersToExpire) > 1 ? 's' : '')
            . ' due to expire. Proceeding to cancel...',
            Logger::SOFT_FAILED_LOG
        );

        foreach ($ordersToExpire as $order) {
            if ($this->dataHelper->wasOrderPaidWithBrippo($order)) {
                try {
                    $this->paymentsHelper->cancelOrder(
                        $order,
                        'Soft fail recovery waiting time expired',
                        BrippoOrder::STATUS_PAYMENT_FAILED
                    );
                } catch (Exception $ex) {
                    $this->logger->log(
                        'Unable to expire order #' . $order->getIncrementId() . ': ' . $ex->getMessage(),
                        Logger::SOFT_FAILED_LOG
                    );
                }
            } else {
                $this->logger->log(
                    'Order #' . $order->getIncrementId() . ' is not a Brippo order.',
                    Logger::SOFT_FAILED_LOG
                );
            }
        }
    }

    /**
     * @param OrderInterface $order
     * @param $customerEmail
     * @param $notificationNumber
     * @return void
     */
    private function sendEmail(OrderInterface $order, $customerEmail, $notificationNumber): void
    {
        try {
            $this->logger->log('Sending notification number ' . $notificationNumber . ' for order #' . $order->getIncrementId() . '...', Logger::SOFT_FAILED_LOG);
            $this->softFailHelper->sendNotification(
                $order,
                $customerEmail,
                $notificationNumber
            );
            $this->logger->log('Email sent!', Logger::SOFT_FAILED_LOG);
        } catch (Exception $ex) {
            $this->logger->log($ex->getMessage(), Logger::SOFT_FAILED_LOG);
        }
    }
//
//    /**
//     * @param OrderInterface $order
//     * @param $customerPhone
//     * @param $scopeId
//     * @return void
//     */
//    private function sendSMS(OrderInterface $order, $customerPhone, $scopeId)
//    {
//        try {
//            if ($this->dataHelper->getStoreConfig(
//                RecoverCheckout::CONFIG_PATH_SEND_SMS,
//                $scopeId
//            )) {
//                $this->logger->log('Sending SMS...', Logger::RECOVER_LOG);
//                $this->sof->sendSMS(
//                    $customerPhone,
//                    $scopeId,
//                    $this->recoverCheckoutHelper->getSMSMessage($order)
//                );
//                $this->logger->log('SMS sent!', Logger::RECOVER_LOG);
//            } else {
//                $this->logger->log('Notification via SMS is disabled.', Logger::RECOVER_LOG);
//            }
//        } catch (Exception $ex) {
//            $this->logger->log($ex->getMessage(), Logger::RECOVER_LOG);
//        }
//    }
//
//    /**
//     * @param OrderInterface $order
//     * @param $customerPhone
//     * @param $scopeId
//     * @return void
//     */
//    private function sendWhatsApp(OrderInterface $order, $customerPhone, $scopeId)
//    {
//        try {
//            if ($this->dataHelper->getStoreConfig(
//                RecoverCheckout::CONFIG_PATH_SEND_WHATSAPP,
//                $scopeId
//            )) {
//                $this->logger->log('Sending WhatsApp...', Logger::RECOVER_LOG);
//                $this->recoverCheckoutHelper->sendWhatsApp(
//                    $customerPhone,
//                    $scopeId,
//                    $this->recoverCheckoutHelper->getWhatsAppMessage($order)
//                );
//                $this->logger->log('WhatsApp sent!', Logger::RECOVER_LOG);
//            } else {
//                $this->logger->log('Notification via WhatsApp is disabled.', Logger::RECOVER_LOG);
//            }
//        } catch (Exception $ex) {
//            $this->logger->log($ex->getMessage(), Logger::RECOVER_LOG);
//        }
//    }
}
