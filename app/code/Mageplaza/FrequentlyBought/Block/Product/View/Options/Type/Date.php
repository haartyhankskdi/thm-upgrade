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

/**
 * Class Date
 *
 * @package Mageplaza\FrequentlyBought\Block\Product\View\Options\Type
 */
class Date extends \Magento\Catalog\Block\Product\View\Options\Type\Date
{
    /**
     * JS Calendar html
     *
     * @return string Formatted Html
     */
    public function getCalendarDateHtml()
    {
        $calendarHtml = parent::getCalendarDateHtml();

        $productId = $this->getProduct()->getId();
        $replaceArray = [
            'options_' . $this->getOption()->getId() . '_date' => 'options_' . $productId . '_' . $this->getOption()->getId() . '_date',
            'options[' . $this->getOption()->getId() . '][date]' => 'options_' . $productId . '[' . $this->getOption()->getId() . '][date]'
        ];

        $calendarHtml = str_replace(array_keys($replaceArray), array_values($replaceArray), $calendarHtml);

        return $calendarHtml;
    }

    /**
     * HTML select element
     *
     * @param string $name Id/name of html select element
     * @param int|null $value
     *
     * @return mixed
     */
    protected function _getHtmlSelect($name, $value = null)
    {
        $select = parent::_getHtmlSelect($name, $value);

        $productId = $this->getProduct()->getId();
        $selectName = 'options_' . $productId . '[' . $this->getOption()->getId() . '][' . $name . ']';
        $extraParams = $select->getExtraParams();

        $extraParams = str_replace(
            'data-selector="' . $select->getName() . '"',
            'data-selector="' . $selectName . '"',
            $extraParams
        );

        $select->setId('options_' . $productId . '_' . $this->getOption()->getId() . '_' . $name)
            ->setName($selectName)
            ->setExtraParams($extraParams);

        return $select;
    }
}
