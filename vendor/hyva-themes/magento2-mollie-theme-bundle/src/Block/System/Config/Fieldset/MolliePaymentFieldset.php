<?php
/**
 * Hyvä Themes - https://hyva.io
 * Copyright © Hyvä Themes 2022-present. All rights reserved.
 * This product is licensed per Magento install
 * See https://hyva.io/license
 */

declare(strict_types=1);

namespace Hyva\MollieThemeBundle\Block\System\Config\Fieldset;

use Magento\Backend\Block\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Config\Block\System\Config\Form\Fieldset;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\View\Helper\Js;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

class MolliePaymentFieldset extends Fieldset
{
    public function __construct(
        Context $context,
        Session $authSession,
        Js $jsHelper,
        SecureHtmlRenderer $secureRenderer,
        array $data = []
    ) {
        parent::__construct($context, $authSession, $jsHelper, $data, $secureRenderer);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getFrontendClass($element)
    {
        return parent::_getFrontendClass($element)
            . ' with-button'
            . ($this->_isCollapseState($element) ? ' open active' : '');
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getHeaderTitleHtml($element)
    {
        $html = <<<EOT
<div class="config-heading">
EOT;

        $html .= '<div class="heading"><strong class="heading-title">'
            . $element->getLegend()
            . '</strong>';

        if ($element->getComment()) {
            $html .= '<div class="heading-intro">'
                . $this->getExtensionDescription()
                . '</div>';
        }
        $logosUrl = 'Hyva_MollieThemeBundle::images/payment-methods/';
        $html .= '<div class="logos">
        <img src="' . $this->getViewFileUrl($logosUrl . 'mastercard.svg') . '" alt="Mastercard">
        <img src="' . $this->getViewFileUrl($logosUrl . 'visa.svg') . '" alt="Visa">
        <img src="' . $this->getViewFileUrl($logosUrl . 'amex.svg') . '" alt="American Express">
        <img src="' . $this->getViewFileUrl($logosUrl . 'paypal.svg') . '" alt="PayPal">
        <img src="' . $this->getViewFileUrl($logosUrl . 'ideal.svg') . '" alt="iDEAL">
        <img src="' . $this->getViewFileUrl($logosUrl . 'klarna.svg') . '" alt="Klarna">
        <img src="' . $this->getViewFileUrl($logosUrl . 'apple-pay.svg') . '" alt="Apple Pay">
        <img src="' . $this->getViewFileUrl($logosUrl . 'google-pay.svg') . '" alt="Google Pay">
        <img src="' . $this->getViewFileUrl($logosUrl . 'sepa.svg') . '" alt="SEPA">
        <img src="' . $this->getViewFileUrl($logosUrl . 'cartes-bancaires.svg') . '" alt="Cartes Bancaires">
</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getHeaderHtml($element)
    {
        if ($element->getIsNested()) {
            $html = '<tr class="nested"><td colspan="4"><div class="'
                . $this->_getFrontendClass($element)
                . '">';
        } else {
            $html = '<div class="'
                . $this->_getFrontendClass($element)
                . '">';
        }

        $html .= '<div class="entry-edit-head admin__collapsible-block">'
            . '<span id="' .
            $element->getHtmlId()
            . '-link" class="entry-edit-head-link"></span>';

        $html .= $this->_getHeaderTitleHtml($element);

        $html .= '</div>';
        $html .= '<input id="'
            . $element->getHtmlId()
            . '-state" name="config_state['
            . $element->getId()
            . ']" type="hidden" value="'
            . $this->_isCollapseState($element)
            . '" />';
        $html .= '<fieldset class="'
            . $this->_getFieldsetCss()
            . '" id="' . $element->getHtmlId()
            . '"><legend>'
            . $element->getLegend()
            . '</legend>';

        $html .= $this->_getHeaderCommentHtml($element);

        $html .= '<table cellspacing="0" class="form-list">'
            . '<colgroup class="label"></colgroup>'
            . '<colgroup class="value"></colgroup>';

        if ($this->getRequest()->getParam('website') || $this->getRequest()->getParam('store')) {
            $html .= '<colgroup class="use-default"></colgroup>';
        }
        $html .= '<colgroup class="scope-label"></colgroup><colgroup class=""></colgroup><tbody>';

        return $html;
    }

    /**
     * @param AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getHeaderCommentHtml($element)
    {
        return '';
    }

    /**
     * Return js code for fieldset:
     *
     * @param AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getExtraJs($element)
    {
        return '';
    }

    private function getExtensionDescription(): string
    {
        $line1 = __("Mollie is a fast-growing payment provider designed for European merchants.");
        $line2 = __("It allows businesses to accept a wide range of global and local payment methods, including credit cards, PayPal, iDEAL, and Klarna, through a single account and contract.");
        $line3 = __("%1 Only pay for successful transactions. Volume pricing for 50K+ a month.", '<strong>' . __("Transparent Pricing") . '</strong>:');
        $line4 = __("%1 Access support in multiple languages with priority for Hyvä merchants.", '<strong>' . __("Multilingual Support") . '</strong>:');
        $line5 = __("%1 Payment Links, Invoicing, 3D Secure, Recurring Payments, Capital, and In-Person Payments", '<strong>' . __("Simplify payments and money management") . '</strong>:');

        return <<<EOD
            <p>$line1</p>
            <p>$line2</p>
            <ul>
                <li>$line3</li>
                <li>$line4</li>
                <li>$line5</li>
            </ul>
            </br>
        EOD;

    }
}
