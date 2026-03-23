<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\ResourceModel;

use Amasty\Gdpr\Api\Data\WithConsentInterface;

class WithConsent extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public const TABLE_NAME = 'amasty_gdpr_consent_log';

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function _construct()
    {
        $this->_init(self::TABLE_NAME, WithConsentInterface::ID);
    }

    /**
     * @param $customerId
     *
     * @return array
     */
    public function getConsentsByCustomerId($customerId)
    {
        $table = $this->getTable(self::TABLE_NAME);
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($table)
            ->where('customer_id = ?', $customerId);

        return $connection->fetchAll($select);
    }
}
