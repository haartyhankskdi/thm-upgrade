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
use Magento\Quote\Model\Quote\Item;
use Mageplaza\RewardPointsUltimate\Helper\Calculation;
use Mageplaza\RewardPointsUltimate\Model\Source\CustomerActions;

/**
 * Class CalculateCustomerDiscount
 * @package Mageplaza\RewardPointsUltimate\Observer
 */
class CalculateCustomerDiscount implements ObserverInterface
{
    /**
     * @var Calculation
     */
    protected $calculation;

    /**
     * CalculateCustomerDiscount constructor.
     *
     * @param Calculation $calculation
     */
    public function __construct(Calculation $calculation)
    {
        $this->calculation = $calculation;
    }

    /**
     * @param EventObserver $observer
     *
     * @return $this|void
     */
    public function execute(EventObserver $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $address = $observer->getEvent()->getShippingAssignment()->getShipping()->getAddress();
        $items = $observer->getEvent()->getItems();
        $fields = [
            'mp_reward_invited_base_discount',
            'mp_reward_invited_discount',
            'mp_reward_shipping_invited_base_discount',
            'mp_reward_shipping_invited_discount'
        ];
        $this->calculation->resetRewardData(
            $items,
            $quote,
            $fields,
            ['mp_reward_invited_base_discount', 'mp_reward_invited_discount']
        );
        $rules = $this->calculation->getReferralsRule($quote, $address);
        if (!$rules) {
            return $this;
        }
        $total = $observer->getEvent()->getTotal();
        foreach ($rules as $rule) {
            if (in_array(
                $rule->getCustomerAction(),
                [CustomerActions::TYPE_FIXED_DISCOUNT, CustomerActions::TYPE_PERCENT]
            )) {
                $totalMathRule = $this->calculation->getTotalMatchRule($quote, $items, $rule, true);
                if ($totalMathRule <= 0) {
                    continue;
                }
                if ($total->getBaseGrandTotal() <= 0) {
                    break;
                }
                $discount = $baseDiscount = 0;
                $discountAmount = $this->calculation->calculateDiscountForCustomer($rule, $totalMathRule);

                $grandTotal = $total->getBaseGrandTotal();
                if (!$rule->getCustomerApplyToShipping()) {
                    $grandTotal -= $this->calculation->getShippingTotalForDiscount($quote, true);
                }
                if ($discountAmount > $grandTotal) {
                    $discountAmount = $grandTotal;
                }
                if ($quote->getMpRewardBaseDiscount() + $discountAmount > $totalMathRule) {
                    $discountAmount = $totalMathRule - $quote->getMpRewardBaseDiscount();
                }

                if ($discountAmount) {
                    foreach ($items as $item) {
                        if ($item->getParentItem()) {
                            continue;
                        }

                        if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                            /** @var Item $child */
                            foreach ($item->getChildren() as $child) {
                                $this->calculation->calculateItemDiscount(
                                    $child,
                                    $totalMathRule,
                                    $discountAmount,
                                    $baseDiscount,
                                    $discount
                                );
                            }
                        } else {
                            $this->calculation->calculateItemDiscount(
                                $item,
                                $totalMathRule,
                                $discountAmount,
                                $baseDiscount,
                                $discount
                            );
                        }
                    }

                    /**
                     * Calculate shipping discount
                     */
                    if ($rule->getCustomerApplyToShipping()) {
                        $baseShippingDiscount = ($this->calculation->getShippingTotalForDiscount(
                            $quote,
                            false
                        ) / $totalMathRule) * $discountAmount;
                        $baseShippingDiscount = $this->calculation->roundPrice($baseShippingDiscount, 'base_refer');
                        $shippingDiscount = $this->calculation->convertPrice(
                            $baseShippingDiscount,
                            false,
                            false,
                            $quote->getStoreId()
                        );
                        $shippingDiscount = $this->calculation->roundPrice($shippingDiscount, 'refer');
                        $quote->setMpRewardShippingInvitedBaseDiscount(
                            $quote->getMpRewardShippingInvitedBaseDiscount() + $baseShippingDiscount
                        );
                        $quote->setMpRewardShippingBaseDiscount(
                            $quote->getMpRewardShippingBaseDiscount() + $baseShippingDiscount
                        )
                            ->setMpRewardShippingDiscount($quote->getMpRewardShippingDiscount() + $shippingDiscount);

                        $baseDiscount += $baseShippingDiscount;
                        $discount += $shippingDiscount;
                    } else {
                        $baseDiscount = $this->calculation->roundPrice($baseDiscount, 'base_refer');
                        $discount = $this->calculation->convertPrice(
                            $baseDiscount,
                            false,
                            false,
                            $quote->getStoreId()
                        );
                        $discount = $this->calculation->roundPrice($discount, 'refer');
                    }

                    $quote->setMpRewardInvitedDiscount($quote->getMpRewardInvitedDiscount() + $discount);
                    $quote->setMpRewardInvitedBaseDiscount($quote->getMpRewardInvitedBaseDiscount() + $baseDiscount);
                    $quote->setMpRewardDiscount($quote->getMpRewardDiscount() + $discount)
                        ->setMpRewardBaseDiscount($quote->getMpRewardBaseDiscount() + $baseDiscount);
                    $baseGrandTotal = $total->getBaseGrandTotal() - $baseDiscount;
                    $grandTotal = $total->getGrandTotal() - $discount;
                    if ($grandTotal <= 0.0001) {
                        $baseGrandTotal = $grandTotal = 0;
                    }

                    $total->setBaseGrandTotal($baseGrandTotal);
                    $total->setGrandTotal($grandTotal);
                    $quote->setBaseGrandTotal($baseGrandTotal);
                    $quote->setGrandTotal($grandTotal);
                }

                if ($rule->getStopRulesProcessing()) {
                    break;
                }
            }
        }
    }
}
