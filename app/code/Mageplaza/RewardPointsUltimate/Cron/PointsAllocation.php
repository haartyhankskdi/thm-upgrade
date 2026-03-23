<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
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

namespace Mageplaza\RewardPointsUltimate\Cron;

use Exception;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Mageplaza\RewardPointsUltimate\Helper\Data as HelperData;
use Mageplaza\RewardPointsUltimate\Model\Behavior;
use Mageplaza\RewardPointsUltimate\Model\BehaviorFactory;
use Mageplaza\RewardPointsUltimate\Model\Config\Source\Frequency;
use Mageplaza\RewardPointsUltimate\Model\MilestoneFactory;
use Mageplaza\RewardPointsUltimate\Model\Source\CustomerEvents;
use Mageplaza\RewardPointsUltimate\Model\Source\Status;
use Psr\Log\LoggerInterface;

/**
 * Class PointsAllocation
 * @package Mageplaza\RewardPointsUltimate\Cron
 */
class PointsAllocation
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var BehaviorFactory
     */
    protected $behaviorFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var TimezoneInterface
     */
    protected $date;

    /**
     * @var MilestoneFactory
     */
    protected $milestoneFactory;

    /**
     * PointsAllocation constructor.
     *
     * @param HelperData $helperData
     * @param CustomerFactory $customerFactory
     * @param BehaviorFactory $behaviorFactory
     * @param TimezoneInterface $date
     * @param MilestoneFactory $milestoneFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        HelperData $helperData,
        CustomerFactory $customerFactory,
        BehaviorFactory $behaviorFactory,
        TimezoneInterface $date,
        MilestoneFactory $milestoneFactory,
        LoggerInterface $logger
    ) {
        $this->helperData       = $helperData;
        $this->customerFactory  = $customerFactory;
        $this->behaviorFactory  = $behaviorFactory;
        $this->logger           = $logger;
        $this->date             = $date;
        $this->milestoneFactory = $milestoneFactory;
    }

    /**
     * @return $this
     * @throws Exception
     */
    public function execute()
    {
        if (!$this->helperData->isEnabled()) {
            return $this;
        }
        $now                = $this->date->date()->format('Y-m-d');
        $customers          = $this->customerFactory->create()->getCollection();
        $behaviorCollection = $this->behaviorFactory->create()->getCollection()
            ->addFieldToFilter('point_action', CustomerEvents::POINTS_ALLOCATION)
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('from_date', [['null' => true], ['lteq' => $now]])
            ->addFieldToFilter('to_date', [['null' => true], ['gteq' => $now]]);
        $tier               = $this->milestoneFactory->create();
        if ($behaviorCollection->getSize()) {
            foreach ($customers as $customer) {
                $websiteId = $customer->getWebsiteId();
                /** @var Behavior $behavior */
                $behaviors   = $behaviorCollection->addFieldToFilter('website_ids', $websiteId)
                    ->addFieldToFilter('customer_group_ids', $customer->getGroupId())
                    ->setOrder('sort_order', 'ASC');
                foreach ($behaviors as $behavior) {
                    $pointAmount = $behavior->getPointAmount();
                    $tier->loadByCustomerId($customer->getId());

                    if ($tier->getId()
                        && (int) $tier->getStatus() === Status::ENABLE
                        && $this->helperData->isEnabled()
                        && $this->helperData->getMilestoneConfig('enabled')
                    ) {
                        $pointAmount += $tier->getEarnFixed();
                    }

                    if (!$pointAmount) {
                        continue;
                    }

                    if (!$this->isAllowPointsAllocation($behavior)) {
                        continue;
                    }

                    try {
                        $expireAfter = $behavior->getExpireAfter();
                        $this->helperData->getTransaction()->createTransaction(
                            HelperData::ACTION_POINTS_ALLOCATION,
                            $customer,
                            new DataObject(['point_amount' => $pointAmount, 'expireAfter' => $expireAfter])
                        );
                    } catch (Exception $e) {
                        $this->logger->critical($e->getMessage());
                    }
                }
            }
        }

        return $this;
    }

    /**
     * @param Behavior $behavior
     *
     * @return bool
     */
    protected function isAllowPointsAllocation($behavior)
    {
        $isAllow    = false;
        $frequency  = $behavior->getFrequency();
        $startDay   = $behavior->getStartDay();
        $startDate  = $behavior->getStartDate();
        $startMonth = $behavior->getStartMonth();

        switch ($frequency) {
            case Frequency::WEEKLY:
                $isAllow = date('w') === $startDay;
                break;
            case Frequency::MONTHLY:
                $isAllow = date('j') === $startDate;
                break;
            case Frequency::YEARLY:
                $isAllow = (date('j') === $startDate && date('n') === $startMonth);
        }

        return $isAllow;
    }
}
