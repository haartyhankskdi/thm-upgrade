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

namespace Mageplaza\RewardPointsUltimate\Plugin\Product;

use Exception;
use Magento\Bundle\Model\Option;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Pricing\Render\Amount;
use Magento\Framework\Registry;
use Mageplaza\RewardPointsUltimate\Helper\Data as HelperData;
use Psr\Log\LoggerInterface;

/**
 * Class Points
 * @package Mageplaza\RewardPointsUltimate\Plugin\Product
 */
class Points
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * Points constructor.
     *
     * @param HelperData $helperData
     * @param LoggerInterface $logger
     * @param Registry $registry
     * @param RequestInterface $request
     */
    public function __construct(
        HelperData $helperData,
        LoggerInterface $logger,
        Registry $registry,
        RequestInterface $request
    ) {
        $this->helperData = $helperData;
        $this->logger     = $logger;
        $this->registry   = $registry;
        $this->request    = $request;
    }

    /**
     * @param Amount $subject
     * @param string $result
     *
     * @return float|string
     */
    public function afterToHtml(Amount $subject, $result)
    {
        $productOption = $subject->getSaleableItem()->getOption();
        if (($productOption && $productOption instanceof Option)) {
            return $result;
        }
        try {
            /** @var Product $product */
            $product = $subject->getSaleableItem();
            if ($this->checkIsSellByPoints($product)) {
                if ($subject->getData('price_type') !== 'finalPrice') {
                    return '';
                }
                if ($subject->getData('price_type') === 'finalPrice') {
                    $html = '<span id="mp_sell_by_price">' . $result . '</span>';
                    if ($this->request->getFullActionName() === 'catalog_product_view') {
                        $html .= '<span id="mp_sell_by_points" hidden>'
                            . '<span class="price-container price-final_price"><span class="price">'
                            . $this->helperData->getPointHelper()->format(
                                $subject->getSaleableItem()->getMpRewardSellProduct(),
                                false
                            ) . '</span></span></span>';
                    }

                    return $html . $this->addScriptJs();
                }
            }
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
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
        $currentProduct   = $this->registry->registry('current_product');
        $customerId       = $this->helperData->getCustomerId();
        $customerGroupIds = explode(',', $product->getMpRwCustomerGroup() ?:'');

        try {
            $customerGroupId = $this->helperData->getGroupIdByCustomerId($customerId);
        } catch (Exception $e) {
            $customerGroupId = '0';
        }

        $isEnabled = $product->getMpRwIsActive()
            && $product->getMpRewardSellProduct() > 0
            && in_array($customerGroupId, $customerGroupIds, true)
            && $this->helperData->isEnabled();

        if ($currentProduct && $this->request->getFullActionName() === 'catalog_product_view') {
            return $currentProduct->getSku() === $product->getSku() && $isEnabled;
        }

        return $isEnabled;
    }

    /**
     * @return string
     */
    protected function addScriptJs()
    {
        return '<script type="text/x-magento-init">
                    {
                        "*": {
                            "Mageplaza_RewardPointsUltimate/js/options": {
                                "labelSellByPoints":"' . __('Buy by Points') . '",
                                "labelSellByPrice":"' . __('Buy by Product Price') . '",
                                "action":"' . $this->request->getFullActionName() . '"
                            }
                        }
                    }
                </script>';
    }
}
