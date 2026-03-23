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

namespace Mageplaza\RewardPointsPro\Plugin\Catalog;

use Closure;
use Exception;
use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\RewardPoints\Helper\Point;
use Mageplaza\RewardPointsPro\Model\CatalogRuleFactory;
use Psr\Log\LoggerInterface;

/**
 * Class Earning
 * @package Mageplaza\RewardPointsPro\Plugin\Catalog
 */
class Earning
{
    /**
     * @var Point
     */
    protected $pointHelper;

    /**
     * @var CatalogRuleFactory
     */
    protected $catalogEarning;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * Earning constructor.
     *
     * @param Point $pointHelper
     * @param CatalogRuleFactory $catalogRuleFactory
     * @param LoggerInterface $logger
     * @param ProductFactory $productFactory
     */
    public function __construct(
        Point $pointHelper,
        CatalogRuleFactory $catalogRuleFactory,
        LoggerInterface $logger,
        ProductFactory $productFactory
    ) {
        $this->pointHelper    = $pointHelper;
        $this->catalogEarning = $catalogRuleFactory;
        $this->logger         = $logger;
        $this->productFactory = $productFactory;
    }

    /**
     * @param AbstractProduct $subject
     * @param Closure $proceed
     * @param Product $product
     * @param bool $templateType
     * @param bool $displayIfNoReviews
     *
     * @return string
     * @throws LocalizedException
     */
    public function aroundGetReviewsSummaryHtml(
        AbstractProduct $subject,
        Closure $proceed,
        $product,
        $templateType = false,
        $displayIfNoReviews = false
    ) {
        $result = $proceed($product, $templateType, $displayIfNoReviews);
        $action = $subject->getRequest()->getFullActionName();
        if ($action === 'catalog_category_view') {
            $product = $this->productFactory->create()->load($product->getId());
        }

        $html = '';
        if ($this->pointHelper->isEnabled()
            && (in_array($action, ['catalog_category_view', 'cms_index_index', ''], true)
                || strpos($subject->getRequest()->getFullActionName(), 'dailydeal') !== false)
            && ($pointEarn = $this->catalogEarning->create()->getPointEarnFromRules($product))
        ) {
            try {
                $pointLabel = '<a href="' . $product->getProductUrl() . '">' . $this->pointHelper->format($pointEarn) . '</a>';
                $label      = in_array($product->getTypeId(), ['grouped', 'bundle', 'configurable']) ?
                    __('Earn from %1', $pointLabel) : __('Earn %1', $pointLabel);

                $html = '<div class="catalog-points" style="margin-bottom:12px">';
                $html .= $this->pointHelper->getIconHtml();
                $html .= '<div class="mp-point-label" style="display: inline-block">
                    <span class="points" style="margin-left: 5px">' . $label . '</span></div><div class="clr"></div></div>';
            } catch (Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }

        return $html . $result;
    }
}
