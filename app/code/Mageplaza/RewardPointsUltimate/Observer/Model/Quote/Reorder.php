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

namespace Mageplaza\RewardPointsUltimate\Observer\Model\Quote;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\OrderRepository;
use Mageplaza\RewardPointsUltimate\Helper\Data;

/**
 * Class Reorder
 * @package Mageplaza\RewardPointsUltimate\Observer\Model\Quote
 */
class Reorder implements ObserverInterface
{
    /**
     * @var Http
     */
    protected $request;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * Reorder constructor.
     *
     * @param Http $request
     * @param OrderRepository $orderRepository
     * @param Data $helperData
     */
    public function __construct(
        Http $request,
        OrderRepository $orderRepository,
        Data $helperData
    ) {
        $this->request         = $request;
        $this->orderRepository = $orderRepository;
        $this->helperData      = $helperData;

    }

    /**
     * @param EventObserver $observer
     *
     * @throws LocalizedException
     */
    public function execute(EventObserver $observer)
    {
        $params = $this->request->getParams();
        if (isset($params['order_id'])) {
            $orderItems = $this->orderRepository->get($params['order_id'])->getAllItems();
            $items = $observer->getData('items');
            foreach ($items as $item) {
                foreach ($orderItems as $orderItem) {
                    if ($orderItem->getSku() === $item->getSku() && $orderItem->getMpRewardSellPoints() > 0) {
                        $price = 0;
                        $item->setPrice($price);
                        $item->setCustomPrice($price);
                        $item->setOriginalCustomPrice($price);
                        $item->setBaseOriginalPrice($price);
                        $item->setMpRewardSellPoints($orderItem->getMpRewardSellPoints());
                        $item->getProduct()->setIsSuperMode(true);
                    }
                }
            }
        }
    }
}
