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

namespace Mageplaza\RewardPointsPro\Block\Checkout\Cart\Items;

use Magento\Checkout\Block\Cart\Additional\Info;
use Magento\Framework\View\Element\Template\Context;
use Mageplaza\RewardPoints\Helper\Point;
use Mageplaza\RewardPointsPro\Model\CatalogRuleFactory;

/**
 * Class Earning
 * @package Mageplaza\RewardPointsPro\Block\Checkout\Cart\Items
 */
class Earning extends Info
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
     * Earning constructor.
     *
     * @param Context $context
     * @param CatalogRuleFactory $catalogRuleFactory
     * @param Point $pointHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        CatalogRuleFactory $catalogRuleFactory,
        Point $pointHelper,
        array $data = []
    ) {
        $this->catalogRule = $catalogRuleFactory;
        $this->pointHelper = $pointHelper;

        parent::__construct($context, $data);
    }

    /**
     * @return bool|mixed|string
     */
    public function getPointEarn()
    {
        if (!$this->pointHelper->isEnabled()) {
            return false;
        }
        $pointEarn = $this->catalogRule->create()->getPointEarnFromProduct(
            $this->getItem()->getProduct(),
            $this->getItem()
        );

        return $pointEarn ? $this->pointHelper->format($pointEarn, false) : false;
    }

    /**
     * Get point icon url
     * @return string
     */
    public function getIconHtml()
    {
        return $this->pointHelper->getIconHtml();
    }
}
