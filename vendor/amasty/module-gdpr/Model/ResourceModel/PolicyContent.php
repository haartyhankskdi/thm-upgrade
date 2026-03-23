<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class PolicyContent extends AbstractDb
{
    public const TABLE_NAME = 'amasty_gdpr_privacy_policy_content';

    public function _construct()
    {
        $this->_init(self::TABLE_NAME, 'id');
    }
}
