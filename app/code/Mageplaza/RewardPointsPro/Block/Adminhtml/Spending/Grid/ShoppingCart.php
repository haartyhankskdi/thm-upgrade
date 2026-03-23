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
 * @category    Mageplaza
 * @package     Mageplaza_RewardPointsPro
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsPro\Block\Adminhtml\Spending\Grid;

use Magento\Backend\Block\Widget\Grid\Container;

/**
 * Class ShoppingCart
 * @package Mageplaza\RewardPointsPro\Block\Adminhtml\Spending\Grid
 */
class ShoppingCart extends Container
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_controller = 'spending_shoppingcart';
        $this->_headerText = __('Shopping Cart Spending Rules');
        $this->_addButtonLabel = __('Add New Rule');

        parent::_construct();
    }
}
