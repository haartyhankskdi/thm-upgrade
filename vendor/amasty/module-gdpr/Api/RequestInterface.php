<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Api;

interface RequestInterface
{
    /**
     * @param int
     *
     * @return bool
     */
    public function approveDeleteRequest(int $customerId): bool;

    /**
     * @param int
     * @param string
     *
     * @return void
     */
    public function denyDeleteRequest(int $customerId, string $comment): void;

    /**
     * @return string[]
     */
    public function getUnprocessedRequests(): array;
}
