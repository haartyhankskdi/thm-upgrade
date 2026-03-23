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

class OrderCancellationSuccess implements OrderCancellationSuccessInterface
{
    /**
     * @var string|null
     */
    private $incrementId;

    /**
     * @var string|null
     */
    private $status;

    public function __construct(?string $incrementId = null, ?string $status = null)
    {
        $this->incrementId = $incrementId;
        $this->status = $status;
    }

    public function setIncrementId(?string $incrementId): void
    {
        $this->incrementId = $incrementId;
    }

    public function getIncrementId(): ?string
    {
        return $this->incrementId;
    }

    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }

    public function getStatus(): string
    {
        return $this->status;
    }
}
