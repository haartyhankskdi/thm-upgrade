<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model;

class ActionLoggerFromAdmin extends ActionLogger
{
    public const ADMIN_OPTION_MAPPING = [
        'data_anonymised_by_customer' => 'data_anonymised_by_admin',
        'delete_request_approved' => 'data_deleted_by_admin',
    ];

    public function logAction($action, $customerId = null, $comment = null)
    {
        $action = self::ADMIN_OPTION_MAPPING[$action] ?? $action;

        return parent::logAction($action, $customerId, $comment);
    }
}
