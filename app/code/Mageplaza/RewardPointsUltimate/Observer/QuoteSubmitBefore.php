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
use Magento\Framework\Exception\InputException;
use Mageplaza\RewardPointsUltimate\Helper\Data as HelperData;
use Mageplaza\RewardPointsUltimate\Helper\SellPoint;

/**
 * Class QuoteSubmitBefore
 * @package Mageplaza\RewardPointsUltimate\Observer
 */
class QuoteSubmitBefore implements ObserverInterface
{
    /**
     * @var SellPoint
     */
    protected $sellPoints;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * RewardQuoteSubmitBefore constructor.
     *
     * @param SellPoint $sellPoint
     * @param HelperData $helperData
     */
    public function __construct(
        SellPoint $sellPoint,
        HelperData $helperData
    ) {
        $this->sellPoints = $sellPoint;
        $this->helperData = $helperData;
    }

    /**
     * @param EventObserver $observer
     *
     * @throws InputException
     */
    public function execute(EventObserver $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $order = $observer->getEvent()->getOrder();
        if ($quote->getCustomerId()) {
            if ($this->sellPoints->isValid(0, $quote)) {
                foreach ($order->getItems() as $item) {
                    $quoteItem = $quote->getItemById($item->getQuoteItemId());
                    if ($quoteItem->getMpRewardSellPoints()) {
                        $item->setMpRewardSellPoints(intval($quoteItem->getMpRewardSellPoints()));
                    }
                }
            } else {
                throw new InputException(__('Your balance is not enough to place the order.'));
            }
        }

        /**
         * Convert referral data to order
         */
        if ($quote->getMpRewardReferralId()) {
            if (strlen((string) $order->getMpRewardEarnAfterInvoice()) == 0) {
                $order->setMpRewardEarnAfterInvoice(
                    $this->helperData->isEarnPointAfterInvoiceCreated($quote->getStoreId())
                );
            }
            $order->setMpRewardReferralId($quote->getMpRewardReferralId());
            $order->setMpRewardReferralEarn($quote->getMpRewardReferralEarn());

            foreach ($order->getItems() as $item) {
                $quoteItem = $quote->getItemById($item->getQuoteItemId());
                if (!$quoteItem) {
                    continue;
                }

                $item->setMpRewardReferralEarn($quoteItem->getMpRewardReferralEarn());
            }
        }
    }
}
