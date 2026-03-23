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
 * @package     Mageplaza_Core
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Customize\Plugin;

/**
 * Class MoveMenu
 * @package Mageplaza\Core\Plugin
 */
class LayoutProcessor
{
    /**
     * @param \Mageplaza\Osc\Block\Checkout\LayoutProcessor $subject
     * @param array $jsLayout
     *
     * @return array
     */
    public function afterProcess(\Mageplaza\Osc\Block\Checkout\LayoutProcessor $subject, array $jsLayout)
    {

        $shippingStep = &$jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children'];
        if (isset($shippingStep['shippingAddress']['children']['shipping-address-fieldset']['children'])) {
            $shipping=&$shippingStep['shippingAddress']['children'];
            if (isset($shipping['shipping-address-fieldset']['children']['telephone'])) {

                $shipping['shipping-address-fieldset']['children']['telephone']['validation']['max_text_length'] = 11;
            }
            if (isset($shipping['shipping-address-fieldset']['children']['telephone'])) {
                $shipping['shipping-address-fieldset']['children']['telephone']['validation']['min_text_length'] = 11;
            }
            if (isset($shipping['shipping-address-fieldset']['children']['telephone'])) {
                $shipping['shipping-address-fieldset']['children']['telephone']['validation']['validate-number'] = true;
            }
            if (isset($shipping['shipping-address-fieldset']['children']['firstname'])) {
                $shipping['shipping-address-fieldset']['children']['firstname']['validation']['validate-alpha'] = true;
            }
            if (isset($shipping['shipping-address-fieldset']['children']['lastname'])) {
                $shipping['shipping-address-fieldset']['children']['lastname']['validation']['validate-alpha'] = true;
            }
        }
        if (isset($shippingStep['billingAddress']['children']['billing-address-fieldset']['children'])) {
            $billing = &$shippingStep['billingAddress']['children'];
            if (isset($billing['billing-address-fieldset']['children']['telephone'])) {
                $billing['billing-address-fieldset']['children']['telephone']['validation']['max_text_length'] = 11;
            }if (isset($billing['billing-address-fieldset']['children']['telephone'])) {
                $billing['billing-address-fieldset']['children']['telephone']['validation']['min_text_length'] = 11;
            }if (isset($billing['billing-address-fieldset']['children']['telephone'])) {
                $billing['billing-address-fieldset']['children']['telephone']['validation']['validate-number'] =true;
            }
            if (isset($billing['billing-address-fieldset']['children']['firstname'])) {
                $billing['billing-address-fieldset']['children']['firstname']['validation']['validate-alpha'] =true;
            }
            if (isset($billing['billing-address-fieldset']['children']['lastname'])) {
                $billing['billing-address-fieldset']['children']['lastname']['validation']['validate-alpha'] =true;
            }
        }
        return $jsLayout;
    }
}
