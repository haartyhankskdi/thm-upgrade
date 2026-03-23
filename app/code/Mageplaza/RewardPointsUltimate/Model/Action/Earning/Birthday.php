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

namespace Mageplaza\RewardPointsUltimate\Model\Action\Earning;

use Mageplaza\RewardPoints\Model\Action\Earning;

/**
 * Class Birthday
 * @package Mageplaza\RewardPointsUltimate\Model\Action\Earning
 */
class Birthday extends Earning
{
    const CODE = 'earning_customer_birthday';

    /**
     * @inheritdoc
     */
    public function getActionLabel()
    {
        return __('Customer birthday');
    }

    /**
     * @inheritdoc
     */
    public function getTitle($transaction)
    {
        return __('Earn point(s) for customer birthday');
    }

    /**
     * @return null
     */
    protected function getExpirationDate()
    {
        $expireDate  = null;
        $expireAfter = $this->getActionObject()->getData('expireAfter');
        if ($expireAfter && $expireAfter !== 0) {
            $expireDate = date('Y-m-d H:i:s', strtotime("+{$expireAfter}days"));
        }

        return $expireDate;
    }
}
