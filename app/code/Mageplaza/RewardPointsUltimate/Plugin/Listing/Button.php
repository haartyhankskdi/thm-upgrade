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
 * @package     Mageplaza_RewardPointsUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsUltimate\Plugin\Listing;

use Closure;
use Exception;
use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\RewardPointsUltimate\Helper\Data as HelperData;
use Mageplaza\RewardPointsUltimate\Block\Category\View as CategoryView;

/**
 * Class Button
 * @package Mageplaza\RewardPointsUltimate\Plugin\Listing
 */
class Button
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * Button constructor.
     *
     * @param HelperData $helperData
     */
    public function __construct(
        HelperData $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * @param AbstractProduct $subject
     * @param Closure $proceed
     * @param Product $product
     *
     * @return mixed|string
     * @throws LocalizedException
     */
    public function aroundGetProductDetailsHtml(AbstractProduct $subject, Closure $proceed, $product)
    {
        $result = $proceed($product);

        if ($this->checkIsSellByPoints($product) && $product->isSaleable() && !$this->helperData->isEnabledHyvaTheme()) {
            $result .= $subject->getLayout()
                ->createBlock(CategoryView::class)
                ->setTemplate('Mageplaza_RewardPointsUltimate::category/view/button.phtml')
                ->toHtml();
        }

        return $result;
    }

    /**
     * @param Product $product
     *
     * @return bool
     */
    protected function checkIsSellByPoints($product)
    {
        $customerId       = $this->helperData->getCustomerId();
        $customerGroupIds = explode(',', $product->getMpRwCustomerGroup() ?: '');

        try {
            $customerGroupId = $this->helperData->getGroupIdByCustomerId($customerId);
        } catch (Exception $e) {
            $customerGroupId = '0';
        }

        return $product->getMpRwIsActive()
            && $product->getMpRewardSellProduct() > 0
            && in_array($customerGroupId, $customerGroupIds, true)
            && $this->helperData->isEnabled();
    }
}
