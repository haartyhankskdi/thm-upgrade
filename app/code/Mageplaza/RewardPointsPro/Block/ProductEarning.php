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

namespace Mageplaza\RewardPointsPro\Block;

use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Mageplaza\RewardPoints\Helper\Point;
use Mageplaza\RewardPointsPro\Model\CatalogRuleFactory;

/**
 * Class ProductEarning
 * @package Mageplaza\RewardPointsPro\Block
 */
class ProductEarning extends Template
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var CatalogRuleFactory
     */
    protected $catalogRule;

    /**
     * @var Point
     */
    protected $pointHelper;

    /**
     * ProductEarning constructor.
     *
     * @param Context $context
     * @param CatalogRuleFactory $catalogRuleFactory
     * @param Registry $registry
     * @param Point $pointHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        CatalogRuleFactory $catalogRuleFactory,
        Registry $registry,
        Point $pointHelper,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->catalogRule   = $catalogRuleFactory;
        $this->pointHelper   = $pointHelper;

        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getIconHtml()
    {
        return $this->pointHelper->getIconHtml();
    }

    /**
     * Get current product
     * @return mixed
     */
    public function getCurrentProduct()
    {
        return $this->_coreRegistry->registry('product');
    }

    /**
     * @return float|int
     */
    public function getPointEarn()
    {
        if (!$this->pointHelper->isEnabled()) {
            return false;
        }
        $product   = $this->getCurrentProduct();
        $pointEarn = $this->catalogRule->create()->getPointEarnFromRules($product);

        return $pointEarn ? $this->pointHelper->format($pointEarn) : false;
    }

    /**
     * @return int|null
     */
    public function isCustomerLogin()
    {
        return $this->pointHelper->getAccountHelper()->isCustomerLoggedIn();
    }

    /**
     * @param $product
     * @param $pointEarn
     * @return \Magento\Framework\Phrase
     */
    public function getLable($product, $pointEarn)
    {
        $label = in_array($product->getTypeId(), [Grouped::TYPE_CODE, Type::TYPE_BUNDLE, Configurable::TYPE_CODE]) ?
            __('Earning from %1 for this item', $pointEarn) : __('Earning %1 for this item', $pointEarn);

        return $label;
    }
}
