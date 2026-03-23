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

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\RewardPoints\Model\Transaction;
use Mageplaza\RewardPointsUltimate\Helper\Data;

/**
 * Class AddAccount
 * @package Mageplaza\RewardPointsUltimate\Observer
 */
class AddAccount implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * AddAccount constructor.
     *
     * @param Data $helperData
     */
    public function __construct(
        Data $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * @param EventObserver $observer
     *
     * @throws LocalizedException
     */
    public function execute(EventObserver $observer)
    {
        /** @var Transaction $transaction */
        $customer   = $observer->getData('customer');
        $customerId = $customer->getId();
        $this->helperData->updateTier($customerId);
    }
}
