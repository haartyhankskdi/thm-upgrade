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

namespace Mageplaza\RewardPointsPro\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Mageplaza\RewardPoints\Helper\Data;

/**
 * Class LastItemCatalogRuleEarning
 * @package Mageplaza\RewardPointsPro\Observer
 */
class LastItemCatalogRuleEarning implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * LastItemCatalogRuleEarning constructor.
     *
     * @param Data $helperData
     */
    public function __construct(Data $helperData)
    {
        $this->helperData = $helperData;
    }

    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        $calculation = $observer->getEvent()->getCalculation();
        $lastItem = $calculation->getLastItemMatchRule();
        $deltaCat = $calculation->getDeltaRoundPoint('catalog');
        if ($lastItem && $deltaCat > 0.1) {
            $quote = $observer->getEvent()->getQuote();
            $pointCatalog = $this->helperData->getPointHelper()->round($deltaCat);
            $quote->setMpRewardEarn($quote->getMpRewardEarn() + $pointCatalog);
            $lastItem->setMpRewardEarn($lastItem->getMpRewardEarn() + $pointCatalog);
        }
    }
}
