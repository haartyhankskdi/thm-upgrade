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
use Magento\Sales\Model\Order;
use Mageplaza\RewardPointsUltimate\Helper\Data as HelperData;

/**
 * Class OrderCancel
 * @package Mageplaza\RewardPointsUltimate\Observer
 */
class OrderCancel implements ObserverInterface
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * OrderCancel constructor.
     *
     * @param HelperData $helperData
     */
    public function __construct(HelperData $helperData)
    {
        $this->helperData = $helperData;
    }

    /**
     * @param EventObserver $observer
     *
     * @throws LocalizedException
     */
    public function execute(EventObserver $observer)
    {
        /* @var $order Order */
        $order = $observer->getEvent()->getOrder();
        $pointsCancel = 0;
        foreach ($order->getItems() as $item) {
            $itemPoint = $item->getMpRewardSellPoints();
            if ($itemPoint > 0) {
                $pointsCancel += $itemPoint * $item->getQtyCanceled();
            }
        }

        if ($pointsCancel > 0) {
            $this->helperData->addTransaction(
                HelperData::ACTION_SELL_POINTS_REFUND,
                $order->getCustomerId(),
                $pointsCancel,
                $order
            );
        }
    }
}
