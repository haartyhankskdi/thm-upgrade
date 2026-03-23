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
use Mageplaza\RewardPointsPro\Model\CatalogRuleFactory;

/**
 * Class CatalogRuleEarning
 * @package Mageplaza\RewardPointsPro\Observer
 */
class CatalogRuleEarning implements ObserverInterface
{
    /**
     * @var CatalogRuleFactory
     */
    protected $catalogEarning;

    /**
     * CatalogRuleEarning constructor.
     *
     * @param CatalogRuleFactory $catalogRuleFactory
     */
    public function __construct(CatalogRuleFactory $catalogRuleFactory)
    {
        $this->catalogEarning = $catalogRuleFactory;
    }

    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        $item = $observer->getEvent()->getItem();
        $catalogEarning = $this->catalogEarning->create();
        $catalogEarning->setHelperCalCulation($observer->getEvent()->getSubject());
        $catalogEarning->getPointEarnFromItem($item);
    }
}
