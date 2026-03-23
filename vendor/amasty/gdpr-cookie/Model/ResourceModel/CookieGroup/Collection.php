<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Model\ResourceModel\CookieGroup;

use Amasty\GdprCookie\Model\CookieGroup;
use Amasty\GdprCookie\Model\ResourceModel\AbstractScopedCollection;
use Amasty\GdprCookie\Model\ResourceModel\CookieGroup as CookieGroupResource;
use Amasty\GdprCookie\Setup\Operation\CreateCookieGroupStoreTable;

class Collection extends AbstractScopedCollection
{
    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _construct()
    {
        $this->_init(CookieGroup::class, CookieGroupResource::class);
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    protected function addStoreData(int $storeId)
    {
        if (!$this->getFlag('group_store_data_added')) {
            $this->getSelect()->joinLeft(
                ['store_table' => $this->getTable(CookieGroupResource::STORE_DATA_TABLE_NAME)],
                "main_table.{$this->getIdFieldName()} = store_table.group_id AND store_table.store_id = {$storeId}",
                []
            );

            foreach ($this->mainTableFields as $tableField) {
                $this->addFieldToSelect($tableField, $tableField);
            }

            $this->setFlag('group_store_data_added', true);
        }

        return $this;
    }
}
