<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\VisitorConsentLog;

use Amasty\Gdpr\Model\VisitorConsentLog\ResourceModel\VisitorConsentLog as VisitorConsentLogResource;
use Magento\Framework\Model\AbstractModel;

class VisitorConsentLog extends AbstractModel
{
    public const ID = 'id';
    public const CUSTOMER_ID = 'customer_id';
    public const SESSION_ID = 'session_id';
    public const DATE_CONSENTED = 'date_consented';
    public const POLICY_VERSION = 'policy_version';
    public const WEBSITE_ID = 'website_id';
    public const IP = 'ip';

    public function _construct()
    {
        parent::_construct();

        $this->_init(VisitorConsentLogResource::class);
        $this->setIdFieldName(self::ID);
    }
}
