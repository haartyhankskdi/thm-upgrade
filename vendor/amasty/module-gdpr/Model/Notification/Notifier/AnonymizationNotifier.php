<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\Notification\Notifier;

class AnonymizationNotifier extends AbstractNotifier
{
    public function notify(
        int $customerId,
        array $vars = []
    ): bool {
        $customer = $this->getCustomer($customerId);
        if (!$customer || !$this->configProvider->isAnonymizationNotificationEnabled($customer->getStoreId())) {
            return true;
        }

        $customerName = $this->nameGeneration->getCustomerName($customer);
        $sender = $this->configProvider->getAnonymizationEmailSender($customer->getStoreId());
        $replyTo = $this->configProvider->getAnonymizationEmailReplyTo($customer->getStoreId());
        if (!trim($replyTo)) {
            $result = $this->senderResolver->resolve($sender);
            $replyTo = $result['email'];
        }

        $vars += [
            'customerName' => $customerName
        ];

        return $this->emailSender->sendEmail(
            [[$customer->getEmail(), $customerName]],
            $sender,
            (int)$customer->getStoreId(),
            $this->configProvider->getAnonymizationEmailTemplate($customer->getStoreId()),
            $vars,
            $replyTo
        );
    }
}
