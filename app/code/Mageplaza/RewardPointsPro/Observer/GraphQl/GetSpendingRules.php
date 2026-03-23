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

namespace Mageplaza\RewardPointsPro\Observer\GraphQl;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\RewardPoints\Helper\Data;
use Mageplaza\RewardPointsPro\Model\CartRepository;

/**
 * Class GetSpendingRules
 * @package Mageplaza\RewardPointsPro\Observer\GraphQl
 */
class GetSpendingRules implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var
     */
    protected $cartRepository;

    /**
     * LastItemCatalogRuleEarning constructor.
     *
     * @param Data $helperData
     */
    public function __construct(CartRepository $cartRepository)
    {
        $this->cartRepository = $cartRepository;
    }

    /**
     * @param EventObserver $observer
     *
     * @return $this|void
     * @throws NoSuchEntityException
     */
    public function execute(EventObserver $observer)
    {
        $object = $observer->getEvent()->getObject();
        $quote = $observer->getEvent()->getQuote();

        $object->setRules($this->cartRepository->getSpendingRules($quote));

        return $this;
    }
}
