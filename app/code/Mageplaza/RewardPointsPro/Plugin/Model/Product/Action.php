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

namespace Mageplaza\RewardPointsPro\Plugin\Model\Product;

use Magento\Catalog\Model\Product\Action as ProductAction;
use Mageplaza\RewardPointsPro\Model\Indexer\Product\ProductRuleProcessor;

/**
 * Class Action
 * @package Mageplaza\RewardPointsPro\Plugin\Model\Product
 */
class Action
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
     * @param ProductAction $object
     * @param ProductAction $result
     *
     * @return ProductAction
     */
    public function afterUpdateAttributes(ProductAction $object, ProductAction $result)
    {
        $data = $result->getAttributesData();
        if (!empty($data['price'])) {
            $this->productRuleProcessor->reindexList($result->getProductIds());
        }

        return $result;
    }
}
