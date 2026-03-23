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
use Magento\Cms\Model\Wysiwyg\Config;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Factory;

class Wysiwyg extends Template
{
    /**
     * @var Factory
     */
    private $factoryElement;
    /**
     * @var Config
     */
    private $wysiwygConfig;

    /**
     * @param Context $context
     * @param Factory $factoryElement
     * @param Config $wysiwygConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        Factory $factoryElement,
        Config $wysiwygConfig,
        $data = []
    ) {
        $this->factoryElement = $factoryElement;
        $this->wysiwygConfig = $wysiwygConfig;
        parent::__construct($context, $data);
    }

    /**
     * Prepare chooser element HTML
     *
     * @param AbstractElement $element Form Element
     * @return AbstractElement
     */
    public function prepareElementHtml(AbstractElement $element): AbstractElement
    {
        $editor = $this->factoryElement->create('editor', ['data' => $element->getData()])
            ->setLabel('')
            ->setWysiwyg(true)
            ->setConfig(
                $this->wysiwygConfig->getConfig([
                    'add_variables' => true,
                    'add_widgets' => false,
                    'add_images' => true
                ])
            )
            ->setForceLoad(true)
            ->setForm($element->getForm());

        if ($element->getRequired()) {
            $editor->addClass('required-entry');
        }

        $element->setData(
            'after_element_html',
            $this->_getAfterElementHtml() . $editor->getElementHtml()
        );

        return $element;
    }

    /**
     * @return string
     */
    protected function _getAfterElementHtml(): string
    {
        $html = <<<HTML
            <style>
                .admin__field-control.control .control-value {
                    display: none !important;
                }
            </style>
        HTML;

        return $html;
    }
}
