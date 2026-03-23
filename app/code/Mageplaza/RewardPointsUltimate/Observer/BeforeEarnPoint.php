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
use Mageplaza\RewardPoints\Helper\Point;
use Mageplaza\RewardPoints\Model\Rate;
use Mageplaza\RewardPointsPro\Model\Source\Catalogrule\Earning;
use Mageplaza\RewardPointsUltimate\Helper\Data;
use Mageplaza\RewardPointsUltimate\Model\Milestone;
use Mageplaza\RewardPointsUltimate\Model\MilestoneFactory;
use Mageplaza\RewardPointsUltimate\Model\Source\Status;

/**
 * Class BeforeEarnPoint
 * @package Mageplaza\RewardPointsUltimate\Observer
 */
class BeforeEarnPoint implements ObserverInterface
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
     * @var Data
     */
    protected $helperData;

    /**
     * @var Point
     */
    protected $pointHelper;

    /**
     * BeforeEarnPoint constructor.
     *
     * @param MilestoneFactory $milestoneFactory
     * @param SessionFactory $sessionFactory
     * @param Data $helperData
     * @param Point $pointHelper
     */
    public function __construct(
        MilestoneFactory $milestoneFactory,
        SessionFactory $sessionFactory,
        Data $helperData,
        Point $pointHelper
    ) {

        $this->milestoneFactory = $milestoneFactory;
        $this->sessionFactory = $sessionFactory;
        $this->helperData = $helperData;
        $this->pointHelper = $pointHelper;
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
        $tier->loadByCustomerId($customerId);

        if ($tier->getId()
            && (int)$tier->getStatus() === Status::ENABLE
            && $this->helperData->isEnabled()
            && $this->helperData->getMilestoneConfig('enabled')
        ) {
            switch ($type) {
                case 'earn_behavior':
                    $rule = $observer->getData('rule');
                    $rule->setPointAmount($rule->getPointAmount() + $tier->getEarnFixed());
                    break;
                case 'earn_card':
                    $rule = $observer->getData('rule');
                    $addPoint = $rule->getPointAmount() + $rule->getPointAmount() * $tier->getEarnPercent() / 100;
                    $rule->setPointAmount($addPoint);
                    break;
                case 'earn_catalog':
                    $eventObject = $observer->getData('rule');
                    $rules = $eventObject->getData('rules');
                    if (count($rules)) {
                        foreach ($rules as &$rule) {
                            $rule['point_amount'] += $this->getAmountByRule($rule, $tier);
                        }
                        $eventObject->setData('rules', $rules);
                    }
                    break;
                case 'earn_rate':
                default:
                    /** @var Rate $rate */
                    $rate = $observer->getData('rate');
                    $rate->setPoints((float)($rate->getPoints() * ($tier->getEarnPercent() + 100) / 100));
                    break;
            }
        }
    }

    /**
     * @param $rule
     * @param $tier
     *
     * @return float|int
     */
    protected function getAmountByRule($rule, $tier)
    {
        if ($rule['action'] === Earning::TYPE_FIXED) {
            return $this->pointHelper->round(
                $rule['point_amount'] * $tier->getEarnPercent() / 100
            );
        }

        return $rule['point_amount'] * $tier->getEarnPercent() / 100;
    }
}
