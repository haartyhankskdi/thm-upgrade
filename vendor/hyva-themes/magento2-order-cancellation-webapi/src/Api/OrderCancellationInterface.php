<?php
/**
 * Hyvä Themes - https://hyva.io
 * Copyright © Hyvä Themes 2022-present. All rights reserved.
 * This product is licensed per Magento install
 * See https://hyva.io/license
 */

declare(strict_types=1);

namespace Hyva\OrderCancellationWebapi\Api;

use Hyva\OrderCancellationWebapi\Api\Data\OrderCancellationSuccessInterface;

interface OrderCancellationInterface
{
    /**
     * @param int $customerId
     * @param int $orderId
     * @param string $reason
     * @return \Hyva\OrderCancellationWebapi\Api\Data\OrderCancellationSuccessInterface
     */
    public function cancelById(int $customerId, int $orderId, string $reason): OrderCancellationSuccessInterface;
}
