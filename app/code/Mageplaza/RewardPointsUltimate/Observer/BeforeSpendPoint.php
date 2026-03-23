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

namespace Mageplaza\RewardPointsUltimate\Observer;

use Magento\Customer\Model\SessionFactory;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\RewardPoints\Model\Rate;
use Mageplaza\RewardPointsUltimate\Helper\Data;
use Mageplaza\RewardPointsUltimate\Model\Milestone;
use Mageplaza\RewardPointsUltimate\Model\MilestoneFactory;
use Mageplaza\RewardPointsUltimate\Model\Source\Status;

/**
 * Class BeforeSpendPoint
 * @package Mageplaza\RewardPointsUltimate\Observer
 */
class BeforeSpendPoint implements ObserverInterface
{
    /**
     * @var MilestoneFactory
     */
    protected $milestoneFactory;

    /**
     * @var SessionFactory
     */
    protected $sessionFactory;

    /**
     * @var bool
     */
    protected $isSpent = false;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * BeforeSpendPoint constructor.
     *
     * @param MilestoneFactory $milestoneFactory
     * @param SessionFactory $sessionFactory
     * @param Data $helperData
     */
    public function __construct(
        MilestoneFactory $milestoneFactory,
        SessionFactory $sessionFactory,
        Data $helperData
    ) {

        $this->milestoneFactory = $milestoneFactory;
        $this->sessionFactory = $sessionFactory;
        $this->helperData = $helperData;
    }

    /**
     * @param EventObserver $observer
     *
     * @throws LocalizedException
     */
    public function execute(EventObserver $observer)
    {
        $type = $observer->getData('type');
        /** @var Milestone $tier */
        $tier = $this->milestoneFactory->create();
        $customerId = $observer->getData('customer_id') ?: $this->sessionFactory->create()->getCustomerId();

        if ($customerId
            && !$this->isSpent
            && $this->helperData->isEnabled()
            && $this->helperData->getMilestoneConfig('enabled')
        ) {
            $tier->loadByCustomerId($customerId);
            if ((int)$tier->getStatus() === Status::ENABLE) {
                $this->isSpent = true;
                switch ($type) {
                    case 'spend_rule':
                        $rule = $observer->getData('rule');
                        $rule->setPointAmount($rule->getPointAmount() * (100 - $tier->getSpentPercent()) / 100);
                        break;
                    case 'spend_rate':
                    default:
                        /** @var Rate $rate */
                        $rate = $observer->getData('rate');
                        $rate->setPoints($rate->getPoints() * (100 - $tier->getSpentPercent()) / 100);
                        break;
                }
            }
        }
    }
}
