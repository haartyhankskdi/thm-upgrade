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

namespace Mageplaza\RewardPointsUltimate\Model\Source;

/**
 * Class Pages
 * @package Mageplaza\RewardPointsUltimate\Model\Source
 */
class Pages extends AbstractSource
{
    const HOMEPAGE = 'cms_index_index';
    const PRODUCT_LIST = 'catalog_category_view';
    const PRODUCT_DETAILS = 'catalog_product_view';

    /**
     * Retrieve option array
     *
     * @return string[]
     */
    public static function getOptionArray()
    {
        return [
            self::HOMEPAGE => __('Home Page'),
            self::PRODUCT_LIST => __('Product List'),
            self::PRODUCT_DETAILS => __('Product Details')
        ];
    }

    /**
     * Retrieve option array with empty value
     *
     * @return string[]
     */
    public function toOptionArray()
    {
        $result = [];

        foreach ($this::getOptionArray() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }

        return $result;
    }
}
