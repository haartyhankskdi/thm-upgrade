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
 * @package     Mageplaza_RewardPointsUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsUltimate\Block\Adminhtml\Referral\Grid;

use Magento\Backend\Block\Widget\Grid\Container;

/**
 * Class Invitation
 * @package Mageplaza\RewardPointsUltimate\Block\Adminhtml\Referral\Grid
 */
class Invitation extends Container
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_controller = 'invitation_history';
        $this->_headerText = __('Invitation History');
        parent::_construct();
        $this->removeButton('add');
    }
}
