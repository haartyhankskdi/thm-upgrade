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
 * @package     Mageplaza_RewardPointsUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsUltimate\Plugin\Controller\Adminhtml;

use Mageplaza\RewardPoints\Controller\Adminhtml\AbstractTransaction;
use Mageplaza\RewardPointsUltimate\Helper\Data;

/**
 * Class MassAction
 * @package Mageplaza\RewardPointsUltimate\Plugin\Controller\Adminhtml
 */
class MassAction
{
    /**
     * @param AbstractTransaction $subject
     * @param callable $proceed
     * @param $transaction
     *
     * @return bool
     */
    public function aroundCanProcess(
        AbstractTransaction $subject,
        callable $proceed,
        $transaction
    ) {
        if ($transaction->getActionCode() == Data::ACTION_IMPORT_TRANSACTION) {
            return false;
        }

        return $proceed($transaction);
    }
}
