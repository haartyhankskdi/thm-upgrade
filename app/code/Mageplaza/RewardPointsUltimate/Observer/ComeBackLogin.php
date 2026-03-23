<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_RewardPointsUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsUltimate\Observer;

use DateTime;
use Exception;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Logger as CustomerLogger;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Mageplaza\RewardPoints\Helper\Data as HelperData;
use Mageplaza\RewardPointsUltimate\Helper\Data;
use Mageplaza\RewardPointsUltimate\Model\Behavior;
use Mageplaza\RewardPointsUltimate\Model\BehaviorFactory;
use Mageplaza\RewardPointsUltimate\Model\Source\CustomerEvents;
use Psr\Log\LoggerInterface;

/**
 * Class ComeBackLogin
 * @package Mageplaza\RewardPoints\Observer
 */
class ComeBackLogin implements ObserverInterface
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var CustomerLogger
     */
    protected $customerLogger;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var BehaviorFactory
     */
    protected $behaviorFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * ComeBackLogin constructor.
     *
     * @param HelperData $helperData
     * @param DateTime $dateTime
     * @param BehaviorFactory $behaviorFactory
     * @param LoggerInterface $logger
     * @param CustomerLogger $customerLogger
     */
    public function __construct(
        HelperData $helperData,
        DateTime $dateTime,
        BehaviorFactory $behaviorFactory,
        LoggerInterface $logger,
        CustomerLogger $customerLogger
    ) {
        $this->helperData = $helperData;
        $this->customerLogger = $customerLogger;
        $this->dateTime = $dateTime;
        $this->behaviorFactory = $behaviorFactory;
        $this->logger = $logger;
    }

    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        /** @var Customer $customer */
        $customer = $observer->getEvent()->getCustomer();
        $log = $this->customerLogger->get($customer->getId());
        $rewardAccount = $this->helperData->getAccountHelper()
            ->getByCustomerId($customer->getId());

        /** @var Behavior $behavior */
        $behavior = $this->behaviorFactory->create();
        $behavior->setCustomerWebsiteId($customer->getWebsiteId());
        $behavior = $behavior->getBehaviorRuleByAction(
            CustomerEvents::COMEBACK_LOGIN,
            true,
            $customer->getGroupId()
        );

        $oldTime = $log->getLastLogoutAt() ?: $log->getLastLoginAt();

        $date_diff = round(
            (
                strtotime(
                    $this->dateTime->format('Y-m-d H:i:s')
                )
                - strtotime($oldTime ?: '')
            ) / (60 * 60 * 24)
        );
        $pointAmount = $behavior->getId() ? $behavior->getPointAmount() : false;

        if ($pointAmount
            && $oldTime !== null
            && $rewardAccount->getIsActive()
            && $date_diff >= $behavior->getMinDays()
            && ($behavior->getIsLoop() || (!$behavior->getIsLoop() && !$rewardAccount->getData('is_comeback')))
        ) {
            try {
                $this->helperData->getTransaction()->createTransaction(
                    Data::ACTION_CUSTOMER_COMEBACK,
                    $customer,
                    new DataObject(['point_amount' => $pointAmount])
                );
            } catch (Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }
    }
}
