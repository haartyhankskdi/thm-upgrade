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

namespace Mageplaza\RewardPointsUltimate\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Mageplaza\RewardPointsUltimate\Helper\Data;

/**
 * Class RemoveCancelButton
 * @package Mageplaza\RewardPointsUltimate\Observer
 */
class RemoveCancelButton implements ObserverInterface
{
    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        $subject = $observer->getEvent()->getSubject();
        if ($subject->getTransaction()->getActionCode() == Data::ACTION_IMPORT_TRANSACTION) {
            $subject->removeButton('cancel_transaction');
        }
    }
}
