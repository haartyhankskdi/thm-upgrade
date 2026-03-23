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

namespace Mageplaza\RewardPointsPro\Block\Adminhtml\Earning\Grid;

use Magento\Backend\Block\Widget\Grid\Container;

/**
 * Class Catalog
 * @package Mageplaza\RewardPointsPro\Block\Adminhtml\Earning\Grid
 */
class Catalog extends Container
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();

        $this->_controller = 'earning_catalog';
        $this->_headerText = __('Catalog Earning Rules');
        $this->_addButtonLabel = __('Add New Rule');

        $this->buttonList->add(
            'apply_rules',
            [
                'label' => __('Apply Rules'),
                'onclick' => "location.href='" . $this->getUrl('*/*/applyRules') . "'",
                'class' => 'apply'
            ]
        );
    }
}
