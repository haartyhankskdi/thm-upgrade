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
 * @category  Mageplaza
 * @package   Mageplaza_FrequentlyBought
 * @copyright Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license   https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\FrequentlyBought\Block\Product\View\Options\Type;

use Magento\Catalog\Model\Product\Option;

/**
 * Class Select
 *
 * @package Mageplaza\FrequentlyBought\Block\Product\View\Options\Type
 */
class Select extends \Magento\Catalog\Block\Product\View\Options\Type\Select
{
    /**
     * Return html for control element
     *
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getValuesHtml(): string
    {
        $valueHtml = parent::getValuesHtml();

        $productId = $this->getProduct()->getId();
        $_option = $this->getOption();
        $optionType = $_option->getType();
        if ($optionType === Option::OPTION_TYPE_DROP_DOWN
            || $optionType === Option::OPTION_TYPE_MULTIPLE
        ) {
            $optionId = $_option->getId();
            $replaceArray = [
                'select_' . $optionId => 'select_' . $productId . '-' . $optionId,
                'options[' . $optionId . ']' => 'options_' . $productId . '[' . $optionId . ']'
            ];

            $valueHtml = str_replace(array_keys($replaceArray), array_values($replaceArray), $valueHtml);
        }

        if ($optionType === Option::OPTION_TYPE_RADIO || $optionType === Option::OPTION_TYPE_CHECKBOX) {
            $optionId = $_option->getId();
            $replaceArray = [
                'class="options-list nested"' => 'class="mg-fbt-options-list options-list nested"',
                'options-' . $optionId . '-list' => 'options-' . $productId . '-' . $optionId . '-list',
                'options_' . $optionId => 'options_' . $productId . '_' . $optionId,
                'data-selector="options[' . $optionId . ']" price' => 'data-selector="options' . $productId . '[' . $optionId . ']" price'
            ];

            switch ($optionType) {
                case Option::OPTION_TYPE_RADIO:
                    $replaceArray = array_merge(
                        $replaceArray,
                        [
                            'name="options[' . $optionId . ']"' => 'name="options_' . $productId . '[' . $optionId . ']"',
                            'data-selector="options[' . $optionId . ']"' => 'data-selector="options[' . $productId . '][' . $optionId . ']"'
                        ]
                    );
                    break;
                case Option::OPTION_TYPE_CHECKBOX:
                    $replaceArray = array_merge(
                        $replaceArray,
                        ['checkbox admin__control-checkbox' => 'checkbox admin__control-checkbox mp-fbt-multi-select']
                    );
                    break;
            }

            $valueHtml = str_replace(array_keys($replaceArray), array_values($replaceArray), $valueHtml);
        }

        return $valueHtml;
    }
}
