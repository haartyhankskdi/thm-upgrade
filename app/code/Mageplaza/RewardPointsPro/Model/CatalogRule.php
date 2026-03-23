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

namespace Mageplaza\RewardPointsPro\Model;

use Exception;
use Magento\Bundle\Model\Product\Type as Bundle;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Mageplaza\RewardPointsPro\Api\Data\CatalogRuleExtensionInterface;
use Mageplaza\RewardPointsPro\Api\Data\CatalogRuleInterface;
use Mageplaza\RewardPointsPro\Model\ResourceModel\CatalogRule as ResourceCatalogRule;
use Mageplaza\RewardPointsPro\Model\Source\Catalogrule\Earning;

/**
 * Class CatalogRule
 * @package Mageplaza\RewardPointsPro\Model
 */
class CatalogRule extends Rules implements CatalogRuleInterface
{
    /**
     * Store matched product Ids
     *
     * @var array
     */
    protected $_productIds;

    /**
     * Limitation for products collection
     *
     * @var int|array|null
     */
    protected $_productsFilter = null;

    /**
     * @return void
     */
    public function _construct()
    {
        parent::_construct();

        $this->_init(ResourceCatalogRule::class);
        $this->setIdFieldName('rule_id');
    }

    /**
     * Get conditions instance
     *
     * @return mixed
     */
    public function getConditionsInstance()
    {
        return $this->conditionCombine->create();
    }

    /**
     * Get actions instance
     *
     * @return mixed
     */
    public function getActionsInstance()
    {
        return $this->actionCollectionFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function afterSave()
    {
        $this->bindRuleToEntity($this->getResource(), 'website_ids');
        $this->bindRuleToEntity($this->getResource(), 'customer_group_ids');
        if ($this->isObjectNew()) {
            $this->getMatchingProductIds();
            if (!empty($this->_productIds) && is_array($this->_productIds)) {
                $this->_ruleProductProcessor->reindexList($this->_productIds);
            }
        } else {
            $this->_ruleProductProcessor->getIndexer()->invalidate();
        }

        parent::afterSave();
    }

    /**
     * Check if rule behavior changed
     *
     * @return bool
     */
    public function isRuleBehaviorChanged()
    {
        if (!$this->isObjectNew()) {
            $arrayDiff = $this->dataDiff($this->getOrigData(), $this->getStoredData());
            unset($arrayDiff['name']);
            unset($arrayDiff['description']);
            if (empty($arrayDiff)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get array with data differences
     *
     * @param array $array1
     * @param array $array2
     *
     * @return array
     */
    protected function dataDiff($array1, $array2)
    {
        $result = [];
        foreach ($array1 as $key => $value) {
            if (array_key_exists($key, $array2)) {
                if (is_array($value)) {
                    if ($value != $array2[$key]) {
                        $result[$key] = true;
                    }
                } else {
                    if ($value != $array2[$key]) {
                        $result[$key] = true;
                    }
                }
            } else {
                $result[$key] = true;
            }
        }

        return $result;
    }

    /**
     * Get array of product ids which are matched by rule
     *
     * @return array
     */
    public function getMatchingProductIds()
    {
        if ($this->_productIds === null) {
            $this->_productIds = [];
            $this->setCollectedAttributes([]);

            if ($this->getWebsiteIds()) {
                /** @var $productCollection Collection */
                $productCollection = $this->productCollectionFactory->create();
                $productCollection->addWebsiteFilter($this->getWebsiteIds());
                if ($this->_productsFilter) {
                    $productCollection->addIdFilter($this->_productsFilter);
                }
                $this->getConditions()->collectValidatedAttributes($productCollection);

                $this->resourceIterator->walk(
                    $productCollection->getSelect(),
                    [[$this, 'callbackValidateProduct']],
                    [
                        'attributes' => $this->getCollectedAttributes(),
                        'product'    => $this->productFactory->create()
                    ]
                );
            }
        }

        return $this->_productIds;
    }

    /**
     * Filtering products that must be checked for matching with rule
     *
     * @param int|array $productIds
     *
     * @return void
     */
    public function setProductsFilter($productIds)
    {
        $this->_productsFilter = $productIds;
    }

    /**
     * Returns products filter
     *
     * @return array|int|null
     */
    public function getProductsFilter()
    {
        return $this->_productsFilter;
    }

    /**
     * Callback function for product matching
     *
     * @param array $args
     *
     * @return void
     */
    public function callbackValidateProduct($args)
    {
        $product = clone $args['product'];
        $product->setData($args['row']);

        $websites = $this->_getWebsitesMap();
        $results  = [];

        foreach ($websites as $websiteId => $defaultStoreId) {
            $product->setStoreId($defaultStoreId);
            $results[$websiteId] = $this->getConditions()->validate($product);
        }
        $this->_productIds[$product->getId()] = $results;
    }

    /**
     * Prepare website map
     *
     * @return array
     */
    protected function _getWebsitesMap()
    {
        $map      = [];
        $websites = $this->storeManager->getWebsites();
        foreach ($websites as $website) {
            // Continue if website has no store to be able to create catalog rule for website without store
            if ($website->getDefaultStore() === null) {
                continue;
            }
            $map[$website->getId()] = $website->getDefaultStore()->getId();
        }

        return $map;
    }

    /**
     * @param Product $product
     *
     * @return float
     * @throws LocalizedException
     */
    public function getPointEarnFromRules(Product $product)
    {
        $pointEarn = $this->getPointEarnFromProduct($product);

        return $this->helperData->getPointHelper()->round($pointEarn);
    }

    /**
     * @param null $item
     *
     * @return float
     * @throws LocalizedException
     */
    public function getPointEarnFromItem($item)
    {
        $pointEarn = $this->calculatePointEarnFromRules(null, $item);

        return $this->helperData->getPointHelper()->round($pointEarn);
    }

    /**
     * @param Product $product
     * @param null $item
     *
     * @return float|int
     * @throws LocalizedException
     */
    public function calculatePointEarnFromRules($product, $item = null)
    {
        $pointEarn          = 0;
        $isMaxEarn          = false;
        $price              = 0;
        $finalPrice         = 0;
        $regularPrice       = 0;
        $isEarnPointFromTax = $this->helperData->isEarnPointFromTax();

        if ($item === null) {
            $qty         = 1;
            $productType = $product->getTypeId();
            if ($productType == 'grouped' || $productType == 'bundle') {
                $minPrice     = $product->getPriceInfo()->getPrice('final_price')->getMinimalPrice();
                $regularPrice = $finalPrice = $minPrice->getValue();
            } else {
                $regularPrice = $product->getPriceInfo()->getPrice('regular_price')->getAmount();
                $finalPrice   = $product->getPriceInfo()->getPrice('final_price')->getAmount();
                if ($isEarnPointFromTax) {
                    $regularPrice = $regularPrice->getValue();
                    $finalPrice   = $finalPrice->getValue();
                } else {
                    $regularPrice = $regularPrice->getBaseAmount();
                    $finalPrice   = $finalPrice->getBaseAmount();
                }
            }
        } else {
            $product = $item->getProduct();
            $price   = $isEarnPointFromTax ? $item->getBaseRowTotalInclTax() : $item->getBaseRowTotal();
            $qty     = $item->getQty();
        }

        $eventObject = new DataObject(['rules' => $this->_getRulesFromProduct($product)]);

        $this->_eventManager->dispatch('mpreward_before_earning_points', [
            'rule'        => $eventObject,
            'customer_id' => null,
            'type'        => 'earn_catalog'
        ]);

        $rules = $eventObject->getData('rules');

        try {
            if (count($rules)) {
                foreach ($rules as $rule) {
                    switch ($rule['action']) {
                        case Earning::TYPE_FIXED:
                            $pointEarn += $rule['point_amount'] * $qty;
                            break;
                        case Earning::TYPE_PERCENT:
                            $originalPrice = $this->getOriginalPrice($product);
                            $price         = $this->convertPrice($originalPrice);
                            $pointEarn += ($price / 100 * $rule['point_amount']) * $qty;

                            if ($rule['max_points'] && $rule['max_points'] > 0) {
                                if ($pointEarn > $rule['max_points']) {
                                    $pointEarn = $rule['max_points'];
                                    $isMaxEarn = true;
                                }
                            }
                            break;
                        default:
                            if ($item != null) {
                                if ($rule['action'] === Earning::TYPE_PROFIT) {
                                    $profit = $item->getProduct()->getCost() * $qty;
                                    if ($price > $profit) {
                                        $price -= $profit;
                                    }
                                } else {
                                    $price -= ($item->getBaseDiscountAmount() + $item->getMpRewardBaseDiscount());
                                }
                            } else {
                                if ($rule['action'] === Earning::TYPE_PRICE) {
                                    $price = $finalPrice;
                                }
                                if ($rule['action'] === Earning::TYPE_PROFIT && $product->getCost()) {
                                    $price = $regularPrice - $product->getCost();
                                } else {
                                    $price = $finalPrice;
                                }
                            }
                            $price = $this->convertPrice($price);

                            $earnItem = $this->pointHelper->round(
                                ($rule['point_amount'] * $price) / $rule['money_step']
                            );

                            if ($rule['max_points'] && $rule['max_points'] > 0) {
                                if ($earnItem > $rule['max_points']) {
                                    $earnItem  = $rule['max_points'];
                                    $isMaxEarn = true;
                                }
                            }
                            $pointEarn += $earnItem;

                            break;
                    }

                    if ($rule['action_stop'] || $isMaxEarn) {
                        break;
                    }
                }
                if ($item !== null) {
                    $item->setMpRewardEarnFromCatalog($pointEarn);
                    $pointEarn = $this->helperData->getPointHelper()->round($pointEarn);
                    $item->setMpRewardEarn($item->getMpRewardEarn() + $pointEarn);
                    $this->helperData->getCalculationHelper()->setLastItemMatchRule($item);
                }
            }
        } catch (Exception $e) {
            $this->_logger->critical($e->getMessage());
        }

        return $pointEarn;
    }

    /**
     * @param Product $product
     *
     * @return int
     */
    public function getOriginalPrice($product)
    {
        switch ($product->getTypeId()) {
            case Configurable::TYPE_CODE:
                return $product->getPriceInfo()->getPrice('regular_price')->getMinRegularAmount()->getValue();
            case Bundle::TYPE_CODE:
                return $product->getPriceInfo()->getPrice('regular_price')->getMinimalPrice()->getValue();
            case Grouped::TYPE_CODE:
                $regularPrice = 0;
                $usedProds    = $product->getTypeInstance()->getAssociatedProducts($product);
                foreach ($usedProds as $child) {
                    if ($child->getId() != $product->getId()) {
                        $regularPrice += $child->getPrice();
                    }
                }

                return $regularPrice;
            default:
                return $product->getPriceInfo()->getPrice('regular_price')->getValue();
        }
    }

    /**
     * @param int $amount
     * @param null $store
     *
     * @return float|int
     * @throws NoSuchEntityException
     */
    public function convertPrice($amount = 0, $store = null)
    {
        $fullActionName   = $this->request->getFullActionName();
        $coverActionArray = [
            'checkout_cart_index',
            'cartquickpro_sidebar_updateItemQty',
            'checkout_cart_updatePost',
            'cartquickpro_cart_add',
            'cartquickpro_catalog_product_options',
            'mpRewards_rewards_getpoint',
            '__'
        ];
        if (in_array($fullActionName, $coverActionArray, true)) {
            return $amount;
        }
        if ($store === null) {
            $store = $this->storeManager->getStore()->getStoreId();
        }

        $rate = 0;
        if ($amount != 0) {
            $rate = $this->priceCurrency->convert($amount, $store) / $amount;
        }

        return $amount != 0 ? $amount / $rate : $amount;
    }

    /**
     * @param Product $product
     * @param null $item
     *
     * @return float|int
     * @throws LocalizedException
     */
    public function getPointEarnFromProduct($product, $item = null)
    {
        return $this->calculatePointEarnFromRules($product, $item);
    }

    /**
     * @param Product $product
     *
     * @return mixed
     * @throws LocalizedException
     */
    public function _getRulesFromProduct($product)
    {
        $productId = $product->getId();
        $storeId   = $product->getStoreId();
        $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
        if ($product->hasCustomerGroupId()) {
            $customerGroupId = $product->getCustomerGroupId();
        } else {
            $customerGroupId = 0;
            if ($this->_customerSession->create()->getId()) {
                $customerGroupId = $this->customerFactory->create()->load($this->_customerSession->create()->getId())
                    ->getGroupId();
            }
        }
        $dateTs = $this->_localeDate->scopeTimeStamp($storeId);

        return $this->_getResource()->getRulesFromProduct($dateTs, $websiteId, $customerGroupId, $productId);
    }

    /**
     * {@inheritdoc}
     */
    public function getPointAmount()
    {
        return $this->getData(self::POINT_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setPointAmount($value)
    {
        return $this->setData(self::POINT_AMOUNT, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getMoneyStep()
    {
        return $this->getData(self::MONEY_STEP);
    }

    /**
     * {@inheritdoc}
     */
    public function setMoneyStep($value)
    {
        return $this->setData(self::MONEY_STEP, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxPoints()
    {
        return $this->getData(self::MAX_POINTS);
    }

    /**
     * {@inheritdoc}
     */
    public function setMaxPoints($value)
    {
        return $this->setData(self::MAX_POINTS, $value);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return CatalogRuleExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     *
     * @param CatalogRuleExtensionInterface $extensionAttributes
     *
     * @return $this
     */
    public function setExtensionAttributes(
        CatalogRuleExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
