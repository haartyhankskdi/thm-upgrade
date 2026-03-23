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

use Magento\Framework\Data\Form;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\RewardPoints\Model\Account;
use Mageplaza\RewardPointsUltimate\Helper\Data;
use Mageplaza\RewardPointsUltimate\Model\Milestone;
use Mageplaza\RewardPointsUltimate\Model\MilestoneFactory;

/**
 * Class CustomerForm
 * @package Mageplaza\RewardPointsUltimate\Observer
 */
class CustomerForm implements ObserverInterface
{
    /**
     * @var MilestoneFactory
     */
    protected $milestoneTier;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * CustomerForm constructor.
     *
     * @param MilestoneFactory $milestone
     * @param Data $helperData
     */
    public function __construct(
        MilestoneFactory $milestone,
        Data $helperData
    ) {
        $this->milestoneTier = $milestone;
        $this->helperData = $helperData;
    }

    /**
     * @param EventObserver $observer
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(EventObserver $observer)
    {
        /** @var Form $form */
        $form = $observer->getEvent()->getForm();
        /** @var Account $rewardAccount */
        $rewardAccount = $observer->getEvent()->getAccount();

        if ($rewardAccount && $this->helperData->getMilestoneConfig('enabled')) {
            /** @var Milestone $currentTier */
            $currentTier = $this->milestoneTier->create()->loadByCustomerId($rewardAccount->getCustomerId());
            $groupId = $this->helperData->getGroupIdByCustomerId($rewardAccount->getCustomerId());
            $upTier = $currentTier->loadUpTier(
                $rewardAccount->getTotalOrder(),
                $groupId,
                $this->helperData->getWebsiteId()
            );
            $source = $this->helperData->getSourceMilestoneAction();
            $upPoint = $upTier->getMinPoint() - $rewardAccount->getMilestoneTotalEarningPoints(
                $source,
                $this->helperData->getPeriodDate()
            );

            $upText = $upPoint > 0
                ? '<div>' . __(
                    'Earn <strong>%1</strong> points to rank up <strong>%2</strong>',
                    $upPoint,
                    $upTier->getName()
                ) . '</div>'
                : '';

            $milestone = $form->addFieldset('milestone', ['legend' => __('Milestones')]);
            $milestone->addField('current_tier', 'note', [
                'label' => __('Current Tier:'),
                'text' => '<strong>' . $currentTier->getName() . '</strong><br>'
                    . $upText,
            ]);

        }
    }
}
