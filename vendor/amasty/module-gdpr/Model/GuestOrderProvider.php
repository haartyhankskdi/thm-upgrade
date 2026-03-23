<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Helper\Guest as HelperGuest;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

class GuestOrderProvider
{
    /**
     * @var CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var CollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        CookieManagerInterface $cookieManager,
        CollectionFactory $orderCollectionFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->cookieManager = $cookieManager;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->storeManager = $storeManager;
    }

    public function getGuestOrder(): OrderInterface
    {
        $fromCookie = (string)$this->cookieManager->getCookie(HelperGuest::COOKIE_NAME);
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $cookieData = explode(':', base64_decode($fromCookie), 2);
        $protectCode = $cookieData[0] ?? null;
        $incrementId = $cookieData[1] ?? null;
        if (!empty($protectCode) && !empty($incrementId)) {
            $order = $this->getOrderByIncrementId($incrementId);
            if (hash_equals((string)$order->getProtectCode(), $protectCode)) {
                return $order;
            }
        }

        throw new InputException(__('Invalid guest customer session'));
    }

    private function getOrderByIncrementId($incrementId): OrderInterface
    {
        /** @var OrderCollection $orders */
        $orders = $this->orderCollectionFactory->create();

        /** @var OrderInterface $order */
        $order = $orders->addFieldToSelect('*')
            ->addFieldToFilter(OrderInterface::CUSTOMER_IS_GUEST, 1)
            ->addFieldToFilter(OrderInterface::INCREMENT_ID, $incrementId)
            ->addFieldToFilter(OrderInterface::STORE_ID, $this->storeManager->getStore()->getId())
            ->getFirstItem();

        if (!$order->getId()) {
            throw new InputException(__('Invalid order increment ID'));
        }

        return $order;
    }
}
