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
 * @package     Mageplaza_RewardPro
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsPro\Plugin\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Api\SearchResults;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\RewardPoints\Helper\Point;
use Mageplaza\RewardPointsPro\Model\CatalogRuleFactory;

/**
 * Class ProductRepository
 * @package Mageplaza\RewardPointsPro\Plugin\Product
 */
class ProductRepository
{
    /**
     * @var CatalogRuleFactory
     */
    protected $catalogRule;

    /**
     * @var Point
     */
    protected $pointHelper;

    /**
     * ProductRepository constructor.
     *
     * @param CatalogRuleFactory $catalogRuleFactory
     * @param Point $pointHelper
     */
    public function __construct(
        CatalogRuleFactory $catalogRuleFactory,
        Point $pointHelper
    ) {
        $this->catalogRule = $catalogRuleFactory;
        $this->pointHelper = $pointHelper;
    }

    /**
     * @param ProductRepositoryInterface $subject
     * @param Product $result
     *
     * @return Product
     * @throws LocalizedException
     */

    public function afterGet(
        ProductRepositoryInterface $subject,
        $result
    ) {
        if (!$this->pointHelper->isEnabled()) {
            return $result;
        }

        $this->addEarningData($result);

        return $result;
    }

    /**
     * @param Product $product
     *
     * @throws LocalizedException
     */
    public function addEarningData($product)
    {
        $extensionAttributes = $product->getExtensionAttributes();
        $pointEarn = $this->catalogRule->create()->getPointEarnFromRules($product);
        $extensionAttributes->setMpRewardEarning($pointEarn);
    }

    /**
     * @param ProductRepositoryInterface $subject
     * @param SearchResults $searchCriteria
     *
     * @return SearchResults
     * @throws LocalizedException
     */
    public function afterGetList(
        ProductRepositoryInterface $subject,
        SearchResults $searchCriteria
    ) {
        if (!$this->pointHelper->isEnabled()) {
            return $searchCriteria;
        }

        /** @var Product $entity */
        foreach ($searchCriteria->getItems() as $entity) {
            $this->addEarningData($entity);
        }

        return $searchCriteria;
    }
}
