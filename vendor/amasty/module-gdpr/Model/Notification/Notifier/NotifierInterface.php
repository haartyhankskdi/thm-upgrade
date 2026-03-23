<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\Notification\Notifier;

use Magento\Customer\Api\Data\CustomerInterface;

interface NotifierInterface
{
    /**
     * Performs specific notification action depending on Customer ID.
     *
     * @param int $customerId
     * @param array $vars
     *
     * @return void
     */
    public function notify(
        int $customerId,
        array $vars = []
    ): bool;
}
