<?php
/**
 * Hyvä Themes - https://hyva.io
 * Copyright © Hyvä Themes 2020-present. All rights reserved.
 * See https://hyva.io/license
 */

declare(strict_types=1);

namespace Hyva\Widgets\Block\WidgetConfigs;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;

class Color extends Template
{
    /**
     * @var Factory
     */
    protected $elementFactory;

    /**
     * @var Http
     */
    protected $request;

    /**
     * @param Context $context
     * @param Factory $elementFactory
     * @param Http $request
     * @param array $data
     */
    public function __construct(
        Context $context,
        Factory $elementFactory,
        Http $request,
        $data = []
    ) {
        $this->elementFactory = $elementFactory;
        $this->request = $request;
        parent::__construct($context, $data);
    }

    /**
     * Prepare chooser element HTML
     *
     * @param AbstractElement $element
     * @return AbstractElement
     * @throws LocalizedException
     */
    public function prepareElementHtml(AbstractElement $element): AbstractElement
    {
        $input = $this->elementFactory->create("text", ['data' => $element->getData()]);
        $input->setId($element->getId());
        $input->setForm($element->getForm());
        $input->setClass("colorpicker");
        $colorData = false;
        $value = $element->getData('value');
        $postData = $this->request->getPost();
        $colorPickerId = $this->getData('colorpickerid');
        
        if (count($postData) > 0) {
            $colorData = json_decode($postData['widget'], true);
        }

        $value = $colorData['values'][$colorPickerId] ?? $value;

        if ($element->getRequired()) {
            $input->addClass('required-entry');
        }

        $element->setData('after_element_html', $input->getElementHtml()
            . "<script type='text/javascript'>
                    var elements = document.getElementsByClassName('colorpicker');
                    for(var i = 0; i < elements.length; i++) {
                        var el = document.getElementById('" . $element->getId() . "');
                        el.className = el.className;
                        const attribute = document.createAttribute('data-jscolor')
                        attribute.value = '{hash:true}';
                        el.value = '" . $value . "';
                        el.setAttributeNode(attribute);
                        jscolor.presets.default = {
                            alphaChannel: true,
                            format: 'any',
                            required: false
                        }
                        jscolor.install();
                    }
            </script>");

        return $element;
    }
}
