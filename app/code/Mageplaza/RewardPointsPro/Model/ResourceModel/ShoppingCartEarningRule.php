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

namespace Mageplaza\RewardPointsPro\Model\ResourceModel;

/**
 * Class ShoppingCartEarningRule
 * @package Mageplaza\RewardPointsPro\Model\ResourceModel
 */
class ShoppingCartEarningRule extends Rules
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('mageplaza_reward_shopping_cart', 'rule_id');
    }
}
