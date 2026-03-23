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

use Exception;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order\Invoice;
use Mageplaza\RewardPoints\Helper\Data;
use Mageplaza\RewardPointsUltimate\Helper\Data as HelperData;

/**
 * Class SalesOrderInvoiceSaveAfter
 * @package Mageplaza\RewardPoints\Observer
 */
class SalesOrderInvoiceSaveAfter implements ObserverInterface
{
    /**
     * @var Data|HelperData
     */
    protected $helperData;

    /**
     * SalesOrderInvoiceSaveAfter constructor.
     *
     * @param HelperData $helperData
     */
    public function __construct(HelperData $helperData)
    {
        $this->helperData = $helperData;
    }

    /**
     * @param EventObserver $observer
     *
     * @throws Exception
     */
    public function execute(EventObserver $observer)
    {
        /* @var $invoice Invoice */
        $invoice = $observer->getEvent()->getInvoice();
        $order = $invoice->getOrder();
        if ($order->getMpRewardEarnAfterInvoice()) {
            $this->helperData->calculateReferralPoints($order, $invoice, HelperData::ACTION_REFERRAL_EARNING);
        }
        if ($this->helperData->isEarnPointAfterInvoiceCreated()) {
            $this->helperData->updateTier($order->getCustomerId());
        }
    }
}
