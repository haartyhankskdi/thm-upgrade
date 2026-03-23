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

namespace Mageplaza\RewardPointsPro\Plugin\Indexer\Product;

use Mageplaza\RewardPointsPro\Model\Indexer\Rule\RuleProductProcessor;

/**
 * Class Attribute
 * @package Mageplaza\RewardPointsPro\Plugin\Indexer\Product
 */
class Attribute
{
    /**
     * @var RuleProductProcessor
     */
    protected $ruleProductProcessor;

    /**
     * Attribute constructor.
     *
     * @param RuleProductProcessor $ruleProductProcessor
     */
    public function __construct(RuleProductProcessor $ruleProductProcessor)
    {
        $this->ruleProductProcessor = $ruleProductProcessor;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $subject
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute
     *
     * @return \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    public function afterSave(
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $subject,
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute
    ) {
        $this->ruleProductProcessor->markIndexerAsInvalid();

        return $attribute;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $subject
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute
     *
     * @return \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    public function afterDelete(
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $subject,
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute
    ) {
        $this->ruleProductProcessor->markIndexerAsInvalid();

        return $attribute;
    }
}
