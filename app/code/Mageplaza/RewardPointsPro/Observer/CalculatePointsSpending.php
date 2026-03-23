<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_RewardPointsPro
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsPro\Observer;

use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\RewardPoints\Helper\Calculation;
use Mageplaza\RewardPoints\Model\AccountFactory as RewardCustomer;
use Mageplaza\RewardPointsPro\Model\ShoppingCartSpendingRuleFactory;
use Mageplaza\RewardPointsPro\Model\Source\ShoppingCart\Actions;
use Mageplaza\RewardPointsPro\Model\Source\ShoppingCart\DiscountStyle;

/**
 * Class CalculatePointsSpending
 * @package Mageplaza\RewardPointsPro\Observer
 */
class CalculatePointsSpending implements ObserverInterface
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var ShoppingCartSpendingRuleFactory
     */
    protected $shoppingCartSpendingRuleFactory;

    /**
     * @var Calculation
     */
    protected $calculation;

    /**
     * @var int
     */
    protected $baseTotalWithoutDiscountInvited = 0;

    /**
     * @var RewardCustomer
     */
    protected $rewardCustomer;

    /**
     * @var ManagerInterface
     */
    protected $_eventManager;

    /**
     * CalculatePointsSpending constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param ShoppingCartSpendingRuleFactory $shoppingCartSpendingRuleFactory
     * @param Calculation $calculation
     * @param ManagerInterface $_eventManager
     * @param RewardCustomer $rewardCustomer
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        PriceCurrencyInterface $priceCurrency,
        ShoppingCartSpendingRuleFactory $shoppingCartSpendingRuleFactory,
        Calculation $calculation,
        ManagerInterface $_eventManager,
        RewardCustomer $rewardCustomer
    ) {
        $this->storeManager = $storeManager;
        $this->priceCurrency = $priceCurrency;
        $this->shoppingCartSpendingRuleFactory = $shoppingCartSpendingRuleFactory;
        $this->calculation = $calculation;
        $this->rewardCustomer = $rewardCustomer;
        $this->_eventManager = $_eventManager;
    }

    /**
     * @param EventObserver $observer
     *
     * @return $this|void
     * @throws LocalizedException
     */
    public function execute(EventObserver $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $ruleApplied = $quote->getMpRewardApplied();
        if ($ruleApplied && !in_array($ruleApplied, ['rate', 'no_apply'])) {
            $rule = $this->shoppingCartSpendingRuleFactory->create()->load($ruleApplied);

            $this->_eventManager->dispatch('mpreward_before_spending_points', [
                'rule' => $rule,
                'customer_id' => $quote->getCustomerId(),
                'type' => 'spend_rule'
            ]);

            if (!$rule->getId() || !$rule->getIsActive()) {
                $this->calculation->addLocalizedException($quote);

                return $this;
            }

            $pointSpent = $quote->getMpRewardSpent();

            if ($rule->getAction() === Actions::TYPE_FIXED) {
                $pointSpent = $rule->getPointAmount();
            } else {
                $rewardCustomer = $this->rewardCustomer->create()->loadByCustomerId($quote->getCustomerId());
                $pointBalance = $rewardCustomer->getBalance();
                $maxPoints = max($pointBalance, $rule->getMaxPoints());

                if ($pointSpent > $pointBalance) {
                    $pointSpent = $maxPoints;
                }
            }

            if ($rule->getId() && $pointSpent > 0) {
                $quote->setMpRewardSpent(0);
                $total = $observer->getEvent()->getTotal();
                $baseTotal = $this->getSpendingTotalMatchRule($quote, $rule);
                if ($baseTotal > 0.01) {
                    $this->baseTotalWithoutDiscountInvited = $this->getSpendingTotalMatchRule($quote, $rule, false);
                    $totalDiscountEstimate = $this->getTotalDiscount($baseTotal, $rule, $pointSpent);

                    $totalSpent = $baseDiscount = $discount = 0;
                    $lastItem = '';

                    $this->calculation->resetDeltaRoundPoint('spent');
                    foreach ($quote->getAllItems() as $item) {
                        if ($item->getParentItemId()) {
                            continue;
                        }

                        if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                            foreach ($item->getChildren() as $child) {
                                $this->processItem(
                                    $child,
                                    $pointSpent,
                                    $totalSpent,
                                    $baseDiscount,
                                    $discount,
                                    $lastItem,
                                    $totalDiscountEstimate
                                );
                            }
                        } else {
                            $this->processItem(
                                $item,
                                $pointSpent,
                                $totalSpent,
                                $baseDiscount,
                                $discount,
                                $lastItem,
                                $totalDiscountEstimate
                            );
                        }
                    }

                    if ($rule->getApplyToShipping()) {
                        $shippingSpent = $pointSpent - $totalSpent;
                        $baseShippingDiscount = $totalDiscountEstimate - $baseDiscount;
                        $shippingDiscount = $this->priceCurrency->convert($baseShippingDiscount);

                        $quote->setMpRewardShippingSpent($quote->getMpRewardShippingSpent() + $shippingSpent)
                            ->setMpRewardShippingBaseDiscount(
                                $quote->getMpRewardShippingBaseDiscount() + $baseShippingDiscount
                            )
                            ->setMpRewardShippingDiscount($quote->getMpRewardShippingDiscount() + $shippingDiscount);

                        $baseDiscount += $baseShippingDiscount;
                        $discount += $shippingDiscount;
                        $totalSpent += $shippingSpent;
                    } elseif ($lastItem && $pointSpent > $totalSpent) {
                        $tmpPoint = $pointSpent - $totalSpent;
                        $lastItem->setMpRewardEarn($lastItem->getMpRewardEarn() + $tmpPoint);
                        $totalSpent += $tmpPoint;
                    }

                    $quote->setMpRewardDiscount($quote->getMpRewardDiscount() + $discount)
                        ->setMpRewardBaseDiscount($quote->getMpRewardBaseDiscount() + $baseDiscount)
                        ->setMpRewardSpent($quote->getMpRewardSpent() + $totalSpent);
                    if ($total->getBaseGrandTotal() < $baseDiscount) {
                        $total->setBaseGrandTotal(0);
                        $total->setGrandTotal(0);
                    } else {
                        $total->setBaseGrandTotal($total->getBaseGrandTotal() - $baseDiscount);
                        $total->setGrandTotal($total->getGrandTotal() - $discount);
                    }
                }
            }
        }
    }

    /**
     * @param $total
     * @param $rule
     * @param $point
     *
     * @return float|int
     */
    public function getTotalDiscount($total, $rule, $point)
    {
        /**
         * Default
         * $rule->getAction() == Actions::TYPE_FIXED && $rule->getDiscountStyle() == DiscountStyle::TYPE_FIXED
         */
        $discount = $rule->getDiscountAmount();
        $ruleAction = $rule->getAction();
        $discountStyle = $rule->getDiscountStyle();

        if ($ruleAction == Actions::TYPE_FIXED && $discountStyle == DiscountStyle::TYPE_PERCENT) {
            $discount = ($rule->getDiscountAmount() / 100) * $total;
        }

        if ($ruleAction == Actions::TYPE_PRICE && $discountStyle == DiscountStyle::TYPE_FIXED) {
            $discount = ($point * $discount) / $rule->getPointAmount();
        }

        if ($ruleAction == Actions::TYPE_PRICE && $discountStyle == DiscountStyle::TYPE_PERCENT) {
            $discount = ($point / $rule->getPointAmount() * $discount) * ($total / 100);
        }

        return min($discount, $total);
    }

    /**
     * @param $item
     * @param $pointSpent
     * @param $totalSpent
     * @param $baseDiscount
     * @param $discount
     * @param $lastItem
     * @param $totalDiscountEstimate
     */
    public function processItem(
        $item,
        $pointSpent,
        &$totalSpent,
        &$baseDiscount,
        &$discount,
        &$lastItem,
        $totalDiscountEstimate
    ) {
        if ($item->getMpValidate()) {
            $baseTotalItemWithoutDiscount = $this->calculation->getItemTotalForDiscount($item, true, false, false);
            $percent = $baseTotalItemWithoutDiscount / $this->baseTotalWithoutDiscountInvited;
            $itemSpent = $this->calculation->deltaRoundPoint($percent * $pointSpent, 'spent');

            $baseDiscountItem = $percent * $totalDiscountEstimate;
            $baseDiscountItem = $this->calculation->roundPrice($baseDiscountItem, 'base');
            $discountItem = $this->calculation->convertPrice($baseDiscountItem, false, false, $item->getStoreId());
            $discountItem = $this->calculation->roundPrice($discountItem);

            $item->setMpRewardSpent($itemSpent)
                ->setMpRewardBaseDiscount($item->getMpRewardBaseDiscount() + $baseDiscountItem)
                ->setMpRewardDiscount($item->getMpRewardDiscount() + $discountItem);
            $baseDiscount += $baseDiscountItem;
            $discount += $discountItem;
            $totalSpent += $itemSpent;

            $item->setMpValidate(false);
            $lastItem = $item;
        }
    }

    /**
     * @param $quote
     * @param $rule
     * @param bool $isCalculateInvitedDiscount
     *
     * @return float|int|mixed
     */
    public function getSpendingTotalMatchRule($quote, $rule, $isCalculateInvitedDiscount = true)
    {
        $totalAllItem = 0;

        /** @var Item $item */
        foreach ($quote->getAllItems() as $item) {
            if ($item->getParentItem()) {
                continue;
            }

            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                /** @var Item $child */
                foreach ($item->getChildren() as $child) {
                    if ($rule->validateRule($child)) {
                        $child->setMpValidate(true);
                        $totalAllItem += $this->calculation->getItemTotalForDiscount(
                            $child,
                            true,
                            false,
                            $isCalculateInvitedDiscount
                        );
                    }
                }
            } else {
                if ($rule->validateRule($item)) {
                    $item->setMpValidate(true);
                    $totalAllItem += $this->calculation->getItemTotalForDiscount(
                        $item,
                        true,
                        false,
                        $isCalculateInvitedDiscount
                    );
                }
            }
        }

        if ($rule->getApplyToShipping()) {
            $totalAllItem += $this->calculation->getShippingTotalForDiscount(
                $quote,
                false,
                $isCalculateInvitedDiscount
            );
        }

        return $totalAllItem;
    }
}
