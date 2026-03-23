<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\Notification;

class NotifiersProvider
{
    public const EVENT_ANONYMIZATION = 'anonymization';
    public const EVENT_APPROVE_DELETION = 'approve_deletion';
    public const EVENT_DENY_DELETION = 'deny_deletion';
    public const EVENT_DELETE_REQUEST_CREATED = 'delete_request_created';
    public const EVENT_POLICY_CHANGE = 'policy_change';

    /**
     * @var array
     */
    private $notifiers;

    public function __construct(
        array $notifiers = []
    ) {
        $this->initializeNotifiers($notifiers);
    }

    /**
     * @param string $event
     * @return Notifier\NotifierInterface[]
     */
    public function get(string $event): array
    {
        return $this->notifiers[$event] ?? [];
    }

    private function initializeNotifiers(array $notifiers): void
    {
        foreach ($notifiers as $event => $eventNotifiers) {
            foreach ($eventNotifiers as $notifier) {
                if (!$notifier instanceof Notifier\NotifierInterface) {
                    throw new \LogicException(
                        sprintf('Notifier must implement %s', Notifier\NotifierInterface::class)
                    );
                }
            }

            $this->notifiers[$event] = $eventNotifiers;
        }
    }
}
