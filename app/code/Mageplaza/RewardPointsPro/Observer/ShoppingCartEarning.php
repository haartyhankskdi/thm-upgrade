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
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\RewardPoints\Helper\Data as HelperData;
use Mageplaza\RewardPointsPro\Model\ResourceModel\ShoppingCartEarningRule\Collection;
use Mageplaza\RewardPointsPro\Model\ShoppingCartEarningRule;
use Mageplaza\RewardPointsPro\Model\Source\ShoppingCart\Actions;

/**
 * Class ShoppingCartEarning
 * @package Mageplaza\RewardPointsPro\Observer
 */
class ShoppingCartEarning implements ObserverInterface
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var Collection
     */
    protected $shoppingCartEarningCollection;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ManagerInterface
     */
    protected $_eventManager;

    /**
     * ShoppingCartEarning constructor.
     *
     * @param HelperData $helperData
     * @param StoreManagerInterface $storeManager
     * @param ManagerInterface $_eventManager
     * @param Collection $collection
     */
    public function __construct(
        HelperData $helperData,
        StoreManagerInterface $storeManager,
        ManagerInterface $_eventManager,
        Collection $collection
    ) {
        $this->helperData                    = $helperData;
        $this->storeManager                  = $storeManager;
        $this->shoppingCartEarningCollection = $collection;
        $this->_eventManager                 = $_eventManager;
    }

    /**
     * @param EventObserver $observer
     *
     * @throws LocalizedException
     */
    public function execute(EventObserver $observer)
    {
        $items           = $observer->getEvent()->getItems();
        $quote           = $observer->getEvent()->getQuote();
        $calculateHelper = $this->helperData->getCalculationHelper();

        $store = $this->storeManager->getStore($quote->getStoreId());
        $rules = $this->shoppingCartEarningCollection
            ->addFieldToFilter('is_active', 1)
            ->setValidationFilter($quote->getCustomerGroupId(), $store->getWebsiteId())
            ->load();
        $calculateHelper->resetDeltaRoundPoint('shopping_cart_earning');
        $address = $observer->getEvent()->getShippingAssignment()->getShipping()->getAddress();
        foreach ($rules as $rule) {
            if (!$rule->canProcessRule($address)) {
                continue;
            }
            $totalPrice = $mpRewardEarn = 0;
            $totalPoint = $this->getPointSummaryMatchRule($quote, $items, $rule, $totalPrice);
            if ($totalPrice <= 0.001) {
                continue;
            }

            if ($totalPoint) {
                $lastItem = '';
                foreach ($items as $item) {
                    if ($item->getParentItem()) {
                        continue;
                    }

                    if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                        /** @var Item $child */
                        foreach ($item->getChildren() as $child) {
                            $mpRewardEarn += $this->calculatePointEarnForItem(
                                $child,
                                $totalPrice,
                                $totalPoint,
                                $lastItem
                            );
                        }
                    } else {
                        $mpRewardEarn += $this->calculatePointEarnForItem($item, $totalPrice, $totalPoint, $lastItem);
                    }
                }

                if ($rule->getApplyToShipping()) {
                    $mpRewardShippingEarn = $totalPoint - $mpRewardEarn;

                    if ($rule->getAction() === Actions::TYPE_FIXED) {
                        $mpRewardShippingEarn = 0;
                    }

                    if ($rule->getAction() !== Actions::TYPE_FIXED && $rule->getAction() !== Actions::TYPE_MULTIPLES) {
                        $mpRewardShippingEarn = $this->helperData->getPointHelper()->round($mpRewardEarn);
                    }

                    if ($rule->getAction() === Actions::TYPE_MULTIPLES) {
                        foreach ($items as $item) {
                            if ($item->getParentItem()) {
                                continue;
                            }

                            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                                /** @var Item $child */
                                foreach ($item->getChildren() as $child) {
                                    if ($mpRewardShippingEarn > 0) {
                                        $child->setMpRewardEarn($child->getMpRewardEarn() + $mpRewardShippingEarn);
                                        $mpRewardShippingEarn = 0;
                                    }
                                }
                            } else {
                                if ($mpRewardShippingEarn > 0) {
                                    $item->setMpRewardEarn($item->getMpRewardEarn() + $mpRewardShippingEarn);
                                    $mpRewardShippingEarn = 0;
                                }
                            }
                        }
                    }

                    $quote->setMpRewardShippingEarn($quote->getMpRewardShippingEarn() + $mpRewardShippingEarn);
                } elseif ($lastItem && $totalPoint > $mpRewardEarn) {
                    $tmpPoint = $this->helperData->getPointHelper()
                        ->round($calculateHelper->getDeltaRoundPoint('shopping_cart_earning'));
                    $lastItem->setMpRewardEarn($lastItem->getMpRewardEarn() + $tmpPoint);
                }

                $quote->setMpRewardEarn($quote->getMpRewardEarn() + $totalPoint);
            }

            if ($rule->getStopRulesProcessing()) {
                break;
            }
        }
    }

    /**
     * @param Quote $quote
     * @param array $items
     * @param ShoppingCartEarningRule $rule
     * @param float $totalPrice
     *
     * @return false|float
     * @throws NoSuchEntityException
     */
    public function getPointSummaryMatchRule($quote, $items, $rule, &$totalPrice)
    {
        $qty = 0;

        $this->_eventManager->dispatch('mpreward_before_earning_points', [
            'rule'        => $rule,
            'customer_id' => $quote->getCustomerId(),
            'type'        => 'earn_card'
        ]);

        foreach ($items as $item) {
            if ($item->getParentItem()) {
                continue;
            }

            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                /** @var Item $child */
                foreach ($item->getChildren() as $child) {
                    $totalPrice += $this->getTotalItem($rule, $child, $qty);
                }
            } else {
                $totalPrice += $this->getTotalItem($rule, $item, $qty);
            }
        }

        if ($rule->getApplyToShipping()) {
            $totalPrice += $this->helperData->getCalculationHelper()->getShippingTotalForDiscount($quote);
        }

        switch ($rule->getAction()) {
            case Actions::TYPE_PRICE:
                $points = $totalPrice * $rule->getPointAmount() / $rule->getMoneyStep();
                break;
            case Actions::TYPE_QTY:
                $points = $qty * $rule->getPointAmount() / $rule->getQtyStep();
                break;
            case Actions::TYPE_MULTIPLES:
                $multiplePoints = $totalPrice * $rule->getPointAmount() / $rule->getMoneyStep();
                $points         = $rule->getPointAmount() * $this->helperData->getPointHelper()->round(($multiplePoints / $rule->getPointAmount()));
                break;
            default: //fixed
                $points = $rule->getPointAmount();
        }

        if ($rule->getMaxPoints()
            && $rule->getMaxPoints() > 0
            && $points > $rule->getMaxPoints()) {
            $points = $rule->getMaxPoints();
        }

        return $this->helperData->getPointHelper()->round($points);
    }

    /**
     * @param ShoppingCartEarningRule $rule
     * @param Item $item
     * @param int $qty
     *
     * @return float|int
     */
    public function getTotalItem($rule, $item, &$qty)
    {
        if ($rule->validateRule($item)) {
            $item->setIsValidateRule(true);
            $qty += $item->getQty();

            return $this->helperData->getCalculationHelper()->getItemTotalForDiscount($item, false);
        }

        return 0;
    }

    /**
     * @param Item $item
     * @param float|int $totalPrice
     * @param float|int $totalPoint
     * @param Item|string $lastItem
     *
     * @return int
     */
    public function calculatePointEarnForItem($item, $totalPrice, $totalPoint, &$lastItem)
    {
        if ($item->getIsValidateRule()) {
            $item->setIsValidateRule(false);
            $calculateHelper = $this->helperData->getCalculationHelper();
            $price           = $calculateHelper->getItemTotalForDiscount($item, false);
            $earningPoints   = $calculateHelper->deltaRoundPoint(
                ($price / $totalPrice) * $totalPoint,
                'shopping_cart_earning'
            );
            $item->setMpRewardEarn($item->getMpRewardEarn() + $earningPoints);
            $lastItem = $item;

            return $item->getMpRewardEarn();
        }

        return 0;
    }
}
