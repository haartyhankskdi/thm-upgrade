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

use Magento\Catalog\Model\Product;
use Mageplaza\RewardPointsPro\Model\Indexer\Product\ProductRuleProcessor;

/**
 * Class ApplyRulesAfterReindex
 * @package Mageplaza\RewardPointsPro\Plugin\Indexer\Product\Save
 */
class ApplyRulesAfterReindex
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
     * Apply catalog rules after product resource model save
     *
     * @param Product $subject
     * @param callable $proceed
     */
    public function aroundReindex(
        Product $subject,
        callable $proceed
    ) {
        $proceed();

        $this->productRuleProcessor->reindexRow($subject->getId());
    }
}
