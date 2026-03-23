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

namespace Mageplaza\RewardPointsPro\Plugin\Helper;

use Closure;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Mageplaza\RewardPoints\Helper\Calculation as CalculationAbstract;
use Mageplaza\RewardPoints\Helper\Account;
use Mageplaza\RewardPointsPro\Model\ShoppingCartSpendingRule;
use Mageplaza\RewardPointsPro\Model\ShoppingCartSpendingRuleFactory;
use Mageplaza\RewardPointsPro\Model\Source\ShoppingCart\DiscountStyle;
use Mageplaza\RewardPointsPro\Model\Source\ShoppingCart\OptionsSpending;
use Mageplaza\RewardPointsPro\Model\Source\ShoppingCart\Type;

/**
 * Class Calculation
 * @package Mageplaza\RewardPointsPro\Helper\Plugin
 */
class Calculation
{
    /**
     * @var ShoppingCartSpendingRuleFactory
     */
    protected $cartSpendingFactory;

    /**
     * @var Account
     */
    protected $account;

    /**
     * Calculation constructor.
     *
     * @param ShoppingCartSpendingRuleFactory $cartSpendingFactory
     * @param Account $account
     */
    public function __construct(ShoppingCartSpendingRuleFactory $cartSpendingFactory, Account $account)
    {
        $this->cartSpendingFactory = $cartSpendingFactory;
        $this->account             = $account;
    }

    /**
     * @param CalculationAbstract $helperCalculation
     * @param Closure $proceed
     * @param Quote $quote
     *
     * @return mixed
     */
    public function aroundGetSpendingConfiguration(
        CalculationAbstract $helperCalculation,
        Closure $proceed,
        $quote
    ) {
        $spendingConfig = $proceed($quote);
        $spendingRules  = $this->cartSpendingFactory->create()->getCollection()
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('rule_type', Type::SHOPPING_CART_SPENDING)
            ->setValidationFilter($quote->getCustomerGroupId(), $quote->getStore()->getWebsiteId())
            ->load();

        $items             = $quote->getItems();
        $isAddRuleNotApply = false;
        $customerBalance   = $this->account->getByCustomerId($quote->getCustomerId())->getPointBalance();
        $address           = $quote->getShippingAddress();
        if ($quote->getIsVirtual()) {
            $address = $quote->getBillingAddress();
        }
        foreach ($spendingRules as $rule) {
            if (!$rule->canProcessRule($address)) {
                continue;
            }

            $total = 0;
            foreach ($items as $item) {
                if ($item->getParentItem()) {
                    continue;
                }

                if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                    foreach ($item->getChildren() as $child) {
                        $total += $this->getTotalItemByRule($rule, $child, $helperCalculation);
                    }
                } else {
                    $total += $this->getTotalItemByRule($rule, $item, $helperCalculation);
                }
            }

            if ($total) {
                if ($rule->getApplyToShipping()) {
                    $total += $helperCalculation->getShippingTotalForDiscount($quote, false, false);
                }

                $total -= $quote->getMpRewardInvitedBaseDiscount();
                if ($total < 0.01) {
                    break;
                }

                $isDisplaySlider = true;
                /**
                 * Not calculated max spending point with action fixed
                 */
                if ($rule->getAction() === OptionsSpending::TYPE_PRICE) {
                    $discountAmount = $rule->getDiscountAmount();
                    if ($rule->getDiscountStyle() == DiscountStyle::TYPE_PERCENT) {
                        $discountAmount = ($rule->getDiscountAmount() * $total) / 100;
                    }

                    $maxSpending = floor(($rule->getPointAmount() / $discountAmount) * $total);
                    if ($rule->getMaxPoints() && $maxSpending > $rule->getMaxPoints()) {
                        $maxSpending = $rule->getMaxPoints();
                    }
                    $maxSpending = $helperCalculation->getMaxSpendingPoints($maxSpending);
                } else {
                    if ($customerBalance < $rule->getPointAmount()) {
                        continue;
                    }

                    $maxSpending     = 1;
                    $isDisplaySlider = false;
                    if (!$isAddRuleNotApply) {
                        $spendingConfig['rules'] = array_merge(
                            [
                                $this->getRuleConfig('no_apply', '', $maxSpending, $isDisplaySlider)
                            ],
                            $spendingConfig['rules']
                        );
                        $isAddRuleNotApply       = true;
                    }
                }

                if ($maxSpending) {
                    $label                     = $rule->getLabelByStoreId($quote->getStoreId());
                    $spendingConfig['rules'][] = $this->getRuleConfig(
                        $rule->getId(),
                        $label,
                        $maxSpending,
                        $isDisplaySlider
                    );
                }
            }
        }

        return $spendingConfig;
    }

    /**
     * @param int $id
     * @param string $label
     * @param int $max
     * @param bool $isDisplaySlider
     *
     * @return array
     */
    public function getRuleConfig($id, $label, $max, $isDisplaySlider)
    {
        return [
            'id'              => $id,
            'label'           => $label,
            'min'             => 0,
            'max'             => $max,
            'step'            => 1,
            'isDisplaySlider' => $isDisplaySlider
        ];
    }

    /**
     * @param ShoppingCartSpendingRule $rule
     * @param Item $item
     * @param CalculationAbstract $helperCalculation
     *
     * @return int
     */
    public function getTotalItemByRule($rule, $item, $helperCalculation)
    {
        /** @var CalculationAbstract $helperCalculation */
        return $rule->validateRule($item) ? $helperCalculation->getItemTotalForDiscount($item, true, false, false) : 0;
    }
}
