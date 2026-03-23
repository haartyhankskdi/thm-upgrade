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
 * Class AdminUpdate
 * @package Mageplaza\RewardPointsUltimate\Observer
 */
class AdminUpdate implements ObserverInterface
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
        $dataObject = $observer->getEvent()->getDataObject();
        $this->helperData->updateTier($dataObject->getCustomerId());
    }
}
