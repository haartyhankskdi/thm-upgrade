<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\Notification\Notifier;

class DeleteRequestCreatedNotifier extends AbstractNotifier
{
    public function notify(
        int $customerId,
        array $vars = []
    ): bool {
        $customer = $this->getCustomer($customerId);
        if (!$customer || !$this->configProvider->isAdminDeleteNotificationEnabled($customer->getStoreId())) {
            return true;
        }

        $receivers = $this->configProvider->getAdminNotificationReciever($customer->getStoreId());
        if (empty($receivers)) {
            return true;
        }

        $customerName = $this->nameGeneration->getCustomerName($customer);
        $sender = $this->configProvider->getAdminNotificationSender($customer->getStoreId());
        $vars += [
            'customerName' => $customerName
        ];

        return $this->emailSender->sendEmail(
            $receivers,
            $sender,
            (int)$customer->getStoreId(),
            $this->configProvider->getAdminNotificationTemplate($customer->getStoreId()),
            $vars
        );
    }
}
