<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\ResourceModel;

use Amasty\Gdpr\Api\Data\ActionLogInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ActionLog extends AbstractDb
{
    public const TABLE_NAME = 'amasty_gdpr_action_log';

    public function _construct()
    {
        $this->_init(self::TABLE_NAME, ActionLogInterface::ID);
    }
}
