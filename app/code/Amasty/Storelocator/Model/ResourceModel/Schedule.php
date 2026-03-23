<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb;

class Schedule extends AbstractDb
{
    public const TABLE_NAME = 'amasty_amlocator_schedule';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, 'id');
    }
}
