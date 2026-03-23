<?php

namespace Ebizmarts\BrippoPayments\Cron;

use DateTime;
use DateTimeZone;
use Ebizmarts\BrippoPayments\Helper\BrippoApi\SendMessage;
use Ebizmarts\BrippoPayments\Helper\RecoverCheckout;
use Exception;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Ebizmarts\BrippoPayments\Helper\Logger;
use Ebizmarts\BrippoPayments\Helper\Data as DataHelper;
use Magento\Sales\Model\Order;
use Magento\Framework\Api\SortOrderBuilder;

class RecoverCheckoutAutomaticNotifications
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

    /**
     * @var RecoverCheckout
     */
    protected $recoverCheckoutHelper;

    protected $timesToNotify;
    protected $notificationFirst;
    protected $notificationSpacing;
    protected $sortOrderBuilder;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Logger $logger
     * @param DataHelper $dataHelper
     * @param RecoverCheckout $recoverCheckoutHelper
     * @param SortOrderBuilder $sortOrderBuilder
     * @throws NoSuchEntityException
     */
    public function __construct(
        OrderRepositoryInterface    $orderRepository,
        SearchCriteriaBuilder       $searchCriteriaBuilder,
        Logger                      $logger,
        DataHelper                  $dataHelper,
        RecoverCheckout             $recoverCheckoutHelper,
        SortOrderBuilder            $sortOrderBuilder
    ) {
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->logger = $logger;
        $this->dataHelper = $dataHelper;
        $this->recoverCheckoutHelper = $recoverCheckoutHelper;
        $this->sortOrderBuilder = $sortOrderBuilder;

        $this->timesToNotify = $this->dataHelper->getStoreConfig(
            RecoverCheckout::XML_PATH_RECOVER_AUTO_NOTIFICATIONS
        );
        $this->notificationFirst = $this->dataHelper->getStoreConfig(
            RecoverCheckout::XML_PATH_RECOVER_AUTO_NOTIF_FIRST
        );
        $this->notificationSpacing = $this->dataHelper->getStoreConfig(
            RecoverCheckout::XML_PATH_RECOVER_AUTO_NOTIF_SPACING
        );
    }

    /**
     * @return void
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function execute()
    {
        if (!$this->dataHelper->getStoreConfig(RecoverCheckout::XML_PATH_RECOVER_CHECKOUT_ACTIVE)) {
            return;
        }

        if ($this->timesToNotify === 0) {
            return;
        }

        $this->logger->log('Running recover checkout automatic notifications...', Logger::CRON_LOG);

        $totalHoursToCheck = $this->timesToNotify * $this->notificationSpacing + $this->notificationFirst;
        if ($this->timesToNotify === 1) {
            $totalHoursToCheck = $this->notificationFirst;
        }
        ++$totalHoursToCheck;
        $this->logger->log('Total hours to check is ' . $totalHoursToCheck, Logger::CRON_LOG);

        $dateFrom = date('Y-m-d H:i:s', strtotime('now - ' . $totalHoursToCheck . ' hours'));
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('state', Order::STATE_CANCELED)
            ->addFilter('created_at', $dateFrom, 'gteq')
            ->addSortOrder($this->sortOrderBuilder
                ->setField('created_at')
                ->setDirection(SortOrder::SORT_DESC)
                ->create())
            ->create();
        $orders = $this->orderRepository->getList($searchCriteria)->getItems();

        if (count($orders) === 0) {
            $this->logger->log(
                'No recoverable orders found.',
                Logger::CRON_LOG
            );
            return;
        }

        $this->logger->log(
            'Found ' . count($orders) . ' order' . (count($orders) > 1 ? 's' : '')
            . ' to recover. Proceeding to analyze...',
            Logger::CRON_LOG
        );
        $ordersProcessed = [];
        foreach ($orders as $order) {
            try {
                $this->logger->logOrderEvent(
                    $order,
                    'Recover checkout automatic notifications check...',
                );

                $scopeId = $order->getStoreId();
                if (!$this->dataHelper->getStoreConfig(RecoverCheckout::XML_PATH_RECOVER_CHECKOUT_ACTIVE, $scopeId)
                    || !$this->dataHelper->isServiceReady($scopeId)) {
                    throw new LocalizedException(__('Service is disabled or not ready.'));
                }

                $customerEmail = $order->getCustomerEmail();
                if (empty($customerEmail) && !empty($order->getBillingAddress()->getEmail())) {
                    $customerEmail = $order->getBillingAddress()->getEmail();
                }
                if (empty($customerEmail)) {
                    throw new LocalizedException(__('Customer email not found'));
                }

                $originalOrderIfDuplicate = $this->wasDuplicateOrderAlreadyProcessed($order, $customerEmail, $ordersProcessed);
                if (!empty($originalOrderIfDuplicate)) {
                    throw new LocalizedException(__('Order is duplicate of order #' . $originalOrderIfDuplicate->getIncrementId() . '.'));
                }

                $ordersProcessed []= $order;
                if ($this->recoverCheckoutHelper->wasCanceledOrderRecovered($order)) {
                    throw new LocalizedException(__('Order was already recovered.'));
                }
                if ($this->isNotificationDue($order)) {
                    /*
                     * EMAIL
                     */
                    $this->sendEmail($order, $customerEmail, $scopeId);

                    /*
                     * SMS & WHATSAPP
                     */
                    $customerPhone = null;
                    if (!empty($order->getBillingAddress()->getTelephone())) {
                        $customerPhone = $order->getBillingAddress()->getTelephone();
                    }
                    if (!empty($customerPhone)) {
                        $this->sendSMS($order, $customerPhone, $scopeId);
                        $this->sendWhatsApp($order, $customerPhone, $scopeId);
                    } else {
                        $this->logger->logOrderEvent(
                            $order,
                            'Customer phone not found',
                        );
                    }
                }
            } catch (Exception $ex) {
                $this->logger->logOrderEvent(
                    $order,
                    $ex->getMessage(),
                );
            }
        }
    }

    /**
     * @param OrderInterface $order
     * @return bool
     * @throws Exception
     */
    private function isNotificationDue(OrderInterface $order): bool
    {
        $createdAt = $order->getCreatedAt();
        $createdAtTime = new DateTime($createdAt, new DateTimeZone('UTC'));
        $createdAtTime->setTimezone(new DateTimeZone(date_default_timezone_get()));
        $currentTime = new DateTime('now', new DateTimeZone(date_default_timezone_get()));
        $interval = $createdAtTime->diff($currentTime);
        $hoursDifference = (int)$interval->days * 24 + $interval->h;

        $isDue = false;
        $notification = 0;
        if ($hoursDifference === (int)$this->notificationFirst) {
            $isDue = true;
            $notification = 1;
        } elseif ($this->timesToNotify > 1
            && $hoursDifference === (int)$this->notificationFirst + (int)$this->notificationSpacing) {
            $isDue = true;
            $notification = 2;
        } elseif ($this->timesToNotify > 2
                && $hoursDifference === (int)$this->notificationFirst + (int)$this->notificationSpacing * 2) {
            $isDue = true;
            $notification = 3;
        } elseif ($this->timesToNotify > 3
                && $hoursDifference === (int)$this->notificationFirst + (int)$this->notificationSpacing * 3) {
            $isDue = true;
            $notification = 4;
        } elseif ($this->timesToNotify > 4
                && $hoursDifference === (int)$this->notificationFirst + (int)$this->notificationSpacing * 4) {
            $isDue = true;
            $notification = 5;
        };

        if (!$isDue) {
            throw new LocalizedException(__('Notification is not due. Hours of difference is ' . $hoursDifference));
        } else {
            $this->logger->logOrderEvent(
                $order,
                'Notification is due (' . $notification . ').'
                . ' Proceeding to notify...'
            );
        }

        return true;
    }

    /**
     * @param OrderInterface $order
     * @param $customerEmailOriginal
     * @param array $ordersAlreadyProcessed
     * @return OrderInterface|null
     */
    private function wasDuplicateOrderAlreadyProcessed(OrderInterface $order, $customerEmailOriginal, Array $ordersAlreadyProcessed) {
        /* @var $orderProcessed OrderInterface */
        foreach ($ordersAlreadyProcessed as $orderProcessed) {
            $customerEmail = $order->getCustomerEmail();
            if (empty($customerEmail) && !empty($order->getBillingAddress()->getEmail())) {
                $customerEmail = $order->getBillingAddress()->getEmail();
            }

            /*
             * QUALIFIES AS DUPLICATE IF SAME EMAIL AND UP TO 1HR TIME DIFF
             */
            if ($customerEmail === $customerEmailOriginal
                && abs(strtotime($order->getCreatedAt()) - strtotime($orderProcessed->getCreatedAt())) <= 3600) {
                return $orderProcessed;
            }
        }
        return null;
    }

    /**
     * @param OrderInterface $order
     * @param $customerEmail
     * @param $scopeId
     * @return void
     */
    private function sendEmail(OrderInterface $order, $customerEmail, $scopeId): void
    {
        try {
            if ($this->dataHelper->getStoreConfig(
                RecoverCheckout::CONFIG_PATH_SEND_EMAIL,
                $scopeId
            )) {
                $this->logger->logOrderEvent(
                    $order,
                'Sending email...'
                );
                $this->recoverCheckoutHelper->sendNotification(
                    $order,
                    $customerEmail,
                    $this->recoverCheckoutHelper->getEmailDefaultMessage($order)
                );
                $this->logger->logOrderEvent(
                    $order,
                    'Email sent!'
                );
            } else {
                $this->logger->logOrderEvent(
                    $order,
                    'Notification via Email is disabled.'
                );
            }
        } catch (Exception $ex) {
            $this->logger->logOrderEvent(
                $order,
                $ex->getMessage()
            );
        }
    }

    /**
     * @param OrderInterface $order
     * @param $customerPhone
     * @param $scopeId
     * @return void
     */
    private function sendSMS(OrderInterface $order, $customerPhone, $scopeId)
    {
        try {
            if ($this->dataHelper->getStoreConfig(
                RecoverCheckout::CONFIG_PATH_SEND_SMS,
                $scopeId
            )) {
                $this->logger->logOrderEvent(
                    $order,
                    'Sending SMS...'
                );
                $this->recoverCheckoutHelper->sendSMS(
                    $customerPhone,
                    $scopeId,
                    $this->recoverCheckoutHelper->getSMSMessage($order)
                );
                $this->logger->logOrderEvent(
                    $order,
                    'SMS sent!'
                );
            } else {
                $this->logger->logOrderEvent(
                    $order,
                    'Notification via SMS is disabled.'
                );
            }
        } catch (Exception $ex) {
            $this->logger->logOrderEvent(
                $order,
                $ex->getMessage()
            );
        }
    }

    /**
     * @param OrderInterface $order
     * @param $customerPhone
     * @param $scopeId
     * @return void
     */
    private function sendWhatsApp(OrderInterface $order, $customerPhone, $scopeId)
    {
        try {
            if ($this->dataHelper->getStoreConfig(
                RecoverCheckout::CONFIG_PATH_SEND_WHATSAPP,
                $scopeId
            )) {
                $this->logger->logOrderEvent(
                    $order,
                    'Sending WhatsApp...'
                );
                $this->recoverCheckoutHelper->sendWhatsApp(
                    $customerPhone,
                    $scopeId,
                    $this->recoverCheckoutHelper->getWhatsAppMessage($order)
                );
                $this->logger->logOrderEvent(
                    $order,
                    'WhatsApp sent!'
                );
            } else {
                $this->logger->logOrderEvent(
                    $order,
                    'Notification via WhatsApp is disabled.'
                );
            }
        } catch (Exception $ex) {
            $this->logger->logOrderEvent(
                $order,
                $ex->getMessage()
            );
        }
    }
}
