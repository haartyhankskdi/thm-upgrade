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

namespace Mageplaza\RewardPointsPro\Plugin\Indexer\Product\Save;

use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Framework\Model\AbstractModel;
use Mageplaza\RewardPointsPro\Model\Indexer\Product\ProductRuleProcessor;

/**
 * Class ApplyRules
 * @package Mageplaza\RewardPointsPro\Plugin\Indexer\Product\Save
 */
class ApplyRules
{
    /**
     * @var ProductRuleProcessor
     */
    protected $productRuleProcessor;

    /**
     * @param ProductRuleProcessor $productRuleProcessor
     */
    public function __construct(ProductRuleProcessor $productRuleProcessor)
    {
        $this->productRuleProcessor = $productRuleProcessor;
    }

    /**
     * Apply catalog earning rules after product resource model save
     *
     * @param Product $subject
     * @param callable $proceed
     * @param AbstractModel $product
     *
     * @return Product
     */
    public function aroundSave(
        Product $subject,
        callable $proceed,
        AbstractModel $product
    ) {
        $productResource = $proceed($product);
        if (!$product->getIsMassupdate()) {
            $this->productRuleProcessor->reindexRow($product->getId());
        }

        return $productResource;
    }
}
