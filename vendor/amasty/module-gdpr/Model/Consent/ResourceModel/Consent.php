<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\Consent\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Consent extends AbstractDb
{
    public const TABLE_NAME = 'amasty_gdpr_consents';

    protected function _construct()
    {
        $this->_init(
            self::TABLE_NAME,
            \Amasty\Gdpr\Model\Consent\Consent::ID
        );
    }
}
