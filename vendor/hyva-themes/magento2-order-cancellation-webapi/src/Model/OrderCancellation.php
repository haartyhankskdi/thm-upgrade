<?php
/**
 * Hyvä Themes - https://hyva.io
 * Copyright © Hyvä Themes 2022-present. All rights reserved.
 * This product is licensed per Magento install
 * See https://hyva.io/license
 */

declare(strict_types=1);

namespace Hyva\OrderCancellationWebapi\Model;

use Hyva\OrderCancellationWebapi\Api\Data\OrderCancellationSuccessInterface;
use Hyva\OrderCancellationWebapi\Api\OrderCancellationInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\OrderCancellation\Model\CancelOrder as CancelOrderAction;
use Magento\OrderCancellation\Model\Config\Config as OrderCancellationConfig;
use Magento\OrderCancellation\Model\CustomerCanCancel;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

class OrderCancellation implements OrderCancellationInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ModuleListInterface
     */
    private $moduleList;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    public function __construct(
        ObjectManagerInterface $objectManager,
        ModuleListInterface $moduleList,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->objectManager = $objectManager;
        $this->moduleList = $moduleList;
        $this->orderRepository = $orderRepository;
    }

    public function cancelById(int $customerId, int $orderId, string $reason): OrderCancellationSuccessInterface
    {
        if (! $this->moduleList->has('Magento_OrderCancellation')) {
            throw new ApiNotAvailableException(__('The OrderCancellation API is not available.'));
        }

        // From here on we know the Magento OrderCancellation module is available and active

        $customerCanCancel = $this->objectManager->get(CustomerCanCancel::class);
        $cancelOrderAction = $this->objectManager->get(CancelOrderAction::class);
        $orderCancellationConfig = $this->objectManager->get(OrderCancellationConfig::class);

        if (empty(trim($reason))) {
            throw new \InvalidArgumentException((string) __('The required input "reason" is missing.'));
        }

        /** @var Order|OrderInterface $order */
        $order = $this->orderRepository->get($orderId);

        if ((int) $order->getCustomerId() !== $customerId) {
            throw new LocalizedException(__('Current user is not authorized to cancel this order'));
        }

        if (!$customerCanCancel->execute($order)) {
            throw new LocalizedException(__('Order already closed, complete, cancelled or on hold'));
        }

        if ($order->hasShipments()) {
            throw new LocalizedException(__('Order with one or more items shipped cannot be cancelled'));
        }

        if (!$orderCancellationConfig->isOrderCancellationEnabledForStore((int)$order->getStoreId())) {
            throw new LocalizedException(__('Order cancellation is not enabled for requested store.'));
        }

        $order = $cancelOrderAction->execute($order, $reason);

        return new OrderCancellationSuccess($order->getIncrementId(), $order->getStatus());
    }
}
