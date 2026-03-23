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

namespace Mageplaza\RewardPointsPro\Observer\GraphQl;

use Magento\Catalog\Model\Product;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\RewardPoints\Helper\Point;
use Mageplaza\RewardPointsPro\Model\CatalogRuleFactory;

/**
 * Class ProductEarningPoints
 * @package Mageplaza\RewardPointsPro\Observer\GraphQl
 */
class ProductEarningPoints implements ObserverInterface
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
     * ProductEarningPoints constructor.
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
     * @param EventObserver $observer
     *
     * @return $this|void
     * @throws LocalizedException
     */
    public function execute(EventObserver $observer)
    {
        $earningObject = $observer->getEvent()->getEarningObject();

        /** @var Product $product */
        $product = $observer->getEvent()->getProduct();
        $pointEarn = $this->catalogRule->create()->getPointEarnFromRules($product);
        $earningObject->setData(
            [
                'earning_point' => $pointEarn,
                'earning_point_format' => $this->pointHelper->format($pointEarn)
            ]
        );

        return $this;
    }
}
