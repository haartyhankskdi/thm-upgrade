<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Model\ResourceModel\Cookie;

use Amasty\GdprCookie\Model\Cookie;
use Amasty\GdprCookie\Model\ResourceModel\AbstractScopedCollection;
use Amasty\GdprCookie\Model\ResourceModel\Cookie as CookieResource;
use Amasty\GdprCookie\Model\ResourceModel\CookieGroup;

class Collection extends AbstractScopedCollection
{
    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _construct()
    {
        $this->_init(Cookie::class, CookieResource::class);
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    protected function addStoreData(int $storeId)
    {
        if (!$this->getFlag('cookie_store_data_added')) {
            $this->getSelect()->joinLeft(
                ['store_table' => $this->getTable(CookieResource::STORE_DATA_TABLE_NAME)],
                "main_table.{$this->getIdFieldName()} = store_table.cookie_id AND store_table.store_id = {$storeId}",
                []
            );

            foreach ($this->mainTableFields as $tableField) {
                $this->addFieldToSelect($tableField, $tableField);
            }

            $this->addOrder('main_table.id', self::SORT_ORDER_ASC);
            $this->setFlag('cookie_store_data_added', true);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function joinGroup()
    {
        if (!$this->getFlag('group_table_joined')) {
            $this->getSelect()->joinLeft(
                ['groups' => $this->getTable(CookieGroup::TABLE_NAME)],
                'main_table.group_id = groups.id',
                ['group' => 'IFNULL(groups.name, "None")']
            );
            $this->setFlag('group_table_joined', true);
        }

        return $this;
    }
}
