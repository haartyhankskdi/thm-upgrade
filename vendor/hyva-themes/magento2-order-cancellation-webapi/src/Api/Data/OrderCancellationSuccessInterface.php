<?php
/**
 * Hyvä Themes - https://hyva.io
 * Copyright © Hyvä Themes 2022-present. All rights reserved.
 * This product is licensed per Magento install
 * See https://hyva.io/license
 */

declare(strict_types=1);

namespace Hyva\OrderCancellationWebapi\Api\Data;

interface OrderCancellationSuccessInterface
{
    /**
     * @param string|null $incrementId
     * @return void
     */
    public function setIncrementId(?string $incrementId): void;

    /**
     * @return string|null
     */
    public function getIncrementId(): ?string;

    /**
     * @param string|null $status
     * @return void
     */
    public function setStatus(?string $status): void;

    /**
     * @return string
     */
    public function getStatus(): string;
}
