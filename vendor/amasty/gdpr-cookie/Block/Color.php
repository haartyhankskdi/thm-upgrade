<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Block;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Config\Block\System\Config\Form\Field;

/**
 * @method Color setColorPickerId(string)
 * @method string getColorPickerId()
 * @method Color setColorPickerValue(string|null $data)
 * @method string|null getColorPickerValue()
 * @method Color setColorPickerHtml(string)
 * @method string getColorPickerHtml()
 * @method Color setHtmlId(string)
 * @method string getHtmlId()
 */

class Color extends Field
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_GdprCookie::colorpicker.phtml';

    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $this->setColorPickerId($element->getHtmlId() . '_colorpicker');
        $this->setColorPickerValue($element->getData('value'));
        $this->setColorPickerHtml($element->getElementHtml());
        $this->setHtmlId($element->getHtmlId());

        return $this->toHtml();
    }
}
