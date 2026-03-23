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

namespace Mageplaza\RewardPointsPro\Block\Adminhtml\Spending\ShoppingCart;

use Magento\Backend\Block\Widget\Form\Container;

/**
 * Class Edit
 * @package Mageplaza\RewardPointsPro\Block\Adminhtml\Spending\ShoppingCart
 */
class Edit extends Container
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_objectId = 'rule_id';
        $this->_blockGroup = 'Mageplaza_RewardPointsPro';
        $this->_controller = 'adminhtml_spending_shoppingCart';

        $this->buttonList->add(
            'save_and_continue_edit',
            [
                'class' => 'save',
                'label' => __('Save and Continue Edit'),
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form']],
                ]
            ],
            30
        );

        parent::_construct();
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('mpreward/spending_shoppingcart/index');
    }
}
