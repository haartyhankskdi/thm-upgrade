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

namespace Mageplaza\RewardPointsUltimate\Block\Milestone;

use Magento\Framework\Exception\LocalizedException;
use Mageplaza\RewardPointsUltimate\Block\Account\TierDashboard;
use Mageplaza\RewardPointsUltimate\Helper\Data;
use Mageplaza\RewardPointsUltimate\Model\Milestone;
use Mageplaza\RewardPointsUltimate\Model\ResourceModel\Milestone\Collection;
use Mageplaza\RewardPointsUltimate\Model\Source\ProgressType;

/**
 * Class Tier
 * @package Mageplaza\RewardPointsUltimate\Block\Milestone
 */
class Tier extends TierDashboard
{
    /**
     * @return array
     */
    public function getAllTier()
    {
        try {
            $customer      = $this->ultimateData->getCustomerById($this->getAccount()->getCustomerId());
            $customerGroup = $customer->getGroupId();
            $websiteId     = $customer->getWebsiteId();
        } catch (LocalizedException $e) {
            $customerGroup = 0;
            $websiteId     = 0;
        }

        /** @var Collection $collection */
        $collection = $this->ultimateData->getTierCollectionByCustomerGroup(
            $customerGroup,
            $this->getAccount()->getTotalOrder(),
            $websiteId
        );
        $items      = [];
        foreach ($collection->getItems() as $key => $item) {
            $items[$item->getMinPoint()] = $item;
        }
        ksort($items);

        return $items;
    }

    /**
     * @return mixed
     */
    public function getEndTierId()
    {
        $listTier = $this->getAllTier();
        $endTier  = end($listTier);

        return $endTier->getId();
    }

    /**
     * @param Milestone $tier
     * @param int $milestonePoint
     *
     * @return bool
     */
    public function checkIsPassStep($tier, $milestonePoint)
    {
        return $tier->getMinPoint() <= $milestonePoint
            && $milestonePoint > 0
            && $tier->getId() !== $this->getEndTierId();
    }

    /**
     * @param Milestone $tier
     * @param int $milestonePoint
     *
     * @return bool
     */
    public function checkIsPassStepCircle($tier, $milestonePoint)
    {
        return $tier->getMinPoint() <= $milestonePoint
            && $milestonePoint >= 0
            && $tier->getId() <= $this->getEndTierId();
    }

    /**
     * @param Milestone $currentTier
     * @param Milestone $upTier
     *
     * @return float|int
     */
    public function getBarPercent($currentTier, $upTier)
    {
        $accountPoint = $this->getMilestonePoint();

        if ($upTier->getMinPoint() - $currentTier->getMinPoint() === 0) {
            return 0;
        }

        return ($accountPoint - $currentTier->getMinPoint()) / ($upTier->getMinPoint() - $currentTier->getMinPoint());
    }

    /**
     * @return mixed
     */
    public function getMilestonePoint()
    {
        return $this->getAccount()->getMilestoneTotalEarningPoints(
            $this->ultimateData->getSourceMilestoneAction(),
            $this->ultimateData->getPeriodDate()
        );
    }

    /**
     * @return array|mixed
     */
    public function getTierBackGround()
    {
        return $this->ultimateData->getMilestoneConfig('background_color');
    }

    /**
     * @return array|mixed
     */
    public function getTierColor()
    {
        return $this->ultimateData->getMilestoneConfig('range_color');
    }

    /**
     * @return string
     */
    public function getAllDescriptions()
    {
        $descriptionAr = [];
        foreach ($this->getAllTier() as $tier) {
            $descriptionAr[$tier->getId()] = $tier->getDescription();
        }

        return Data::jsonEncode($descriptionAr);
    }

    /**
     * @return bool
     */
    public function isDashboard()
    {
        return $this->request->getFullActionName() === 'customer_rewards_index' ? true : false;
    }
}
