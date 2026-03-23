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
 * @package     Mageplaza_RewardPointsPro
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsPro\Block\Adminhtml\Earning\ShoppingCart\Edit\Tabs;

use Mageplaza\RewardPointsPro\Block\Adminhtml\RuleForm\General as GeneralForm;

/**
 * Class General
 * @package Mageplaza\RewardPointsPro\Block\Adminhtml\Earning\ShoppingCart\Edit\Tabs
 */
class General extends GeneralForm
{
    /**
     * @var string
     */
    protected $_modelRegistry = 'shopping_cart_earning_rule';
}
