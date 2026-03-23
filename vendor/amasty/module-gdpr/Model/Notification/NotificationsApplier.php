<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\Notification;

class NotificationsApplier
{
    /**
     * @var NotifiersProvider
     */
    private $notifiersProvider;

    public function __construct(
        NotifiersProvider $notifiersProvider
    ) {
        $this->notifiersProvider = $notifiersProvider;
    }

    public function apply(
        string $event,
        int $customerId,
        array $vars = []
    ): bool {
        $result = true;
        foreach ($this->notifiersProvider->get($event) as $notifier) {
            if (!$notifier->notify($customerId, $vars)) {
                $result = false;
            }
        }

        return $result;
    }
}
