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

use Exception;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;
use Mageplaza\RewardPoints\Helper\Data;
use Mageplaza\RewardPointsUltimate\Helper\Data as HelperData;

/**
 * Class SalesOrderCreditmemoSaveAfter
 * @package Mageplaza\RewardPointsUltimate\Observer
 */
class SalesOrderCreditmemoSaveAfter implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * SalesOrderCreditmemoSaveAfter constructor.
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
     * @throws Exception
     */
    public function execute(EventObserver $observer)
    {
        /* @var $creditmemo Creditmemo */
        $creditmemo = $observer->getEvent()->getCreditmemo();

        if ($this->helperData->isRestorePointAfterRefund($creditmemo->getStoreId())) {
            $order = $creditmemo->getOrder();
            $mpRewardEarnAfterInvoice = (bool)$order->getMpRewardEarnAfterInvoice();

            /**
             * Calculate referral points
             */
            if (($mpRewardEarnAfterInvoice || (!$mpRewardEarnAfterInvoice && (in_array(
                $order->getState(),
                [Order::STATE_COMPLETE, Order::STATE_CLOSED]
            ))))) {
                $this->helperData->calculateReferralPoints($order, $creditmemo, HelperData::ACTION_REFERRAL_REFUND);
            }
            /**
             * Calculate sell point refund
             */
            $pointsRefund = $pointOrdered = $pointTransaction = 0;
            foreach ($creditmemo->getItems() as $item) {
                $orderItem = $item->getOrderItem();
                $pointOrdered += $orderItem->getMpRewardSellPoints() * $orderItem->getQtyOrdered();
                if ($item->getMpRewardSellPoints() > 0) {
                    $pointsRefund += $item->getMpRewardSellPoints() * $item->getQty();
                }
            }

            $pointTransaction = $this->helperData->getTransactionByFilter(
                ['action_code' => HelperData::ACTION_SELL_POINTS_REFUND],
                true,
                false,
                ['field' => 'increment_id', 'value' => $order->getIncrementId()],
                true
            );
            if ($pointsRefund > 0 && (($pointsRefund + $pointTransaction) <= $pointOrdered)) {
                $this->helperData->addTransaction(
                    HelperData::ACTION_SELL_POINTS_REFUND,
                    $order->getCustomerId(),
                    $pointsRefund,
                    $order
                );
            }
        }
    }
}
