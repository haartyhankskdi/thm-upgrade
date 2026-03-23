<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Model\ResourceModel;

use Amasty\GdprCookie\Model\StoreData\Hydrator;
use Amasty\GdprCookie\Model\StoreData\ScopedFieldsProvider;
use Magento\Framework\Model\ResourceModel\Db;

class Cookie extends Db\AbstractDb
{
    public const TABLE_NAME = 'amasty_gdprcookie_cookie';
    public const STORE_DATA_TABLE_NAME = 'amasty_gdprcookie_cookie_store_data';

    /**
     * @var Hydrator
     */
    private $hydrator;

    /**
     * @var ScopedFieldsProvider
     */
    private $scopedFieldsProvider;

    /**
     * Used during object hydration with store data
     * @var int
     */
    private $storeId = 0;

    public function __construct(
        Db\Context $context,
        Hydrator $hydrator,
        ScopedFieldsProvider $scopedFieldsProvider,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->hydrator = $hydrator;
        $this->scopedFieldsProvider = $scopedFieldsProvider;
    }

    public function _construct()
    {
        $this->_init(self::TABLE_NAME, 'id');
    }

    public function load(\Magento\Framework\Model\AbstractModel $cookie, $value, $field = null)
    {
        parent::load($cookie, $value, $field);

        if ($storeId = (int)$this->storeId) {
            $cookieStoreData = $this->getConnection()->fetchRow(
                $this->getConnection()->select()
                    ->from($this->getTable(self::STORE_DATA_TABLE_NAME))
                    ->where('cookie_id = ?', (int)$cookie->getId())
                    ->where('store_id = ?', $storeId)
            );

            if ($cookieStoreData) {
                $this->hydrator->hydrateStoreData($cookie, $cookieStoreData);
            }
        }

        return $this;
    }

    public function save(\Magento\Framework\Model\AbstractModel $cookie)
    {
        if ($storeId = (int)$this->storeId) {
            $cookieStoreData = array_intersect_key(
                $cookie->getData(),
                array_flip($this->scopedFieldsProvider->getScopedFields($this->getMainTable()))
            );
            $cookieStoreData['cookie_id'] = (int)$cookie->getId();
            $cookieStoreData['store_id'] = $storeId;

            $this->getConnection()->beginTransaction();
            $this->getConnection()->delete(
                $this->getTable(self::STORE_DATA_TABLE_NAME),
                sprintf('cookie_id = %s AND store_id = %s', (int)$cookie->getId(), $storeId)
            );
            $this->getConnection()->insertOnDuplicate(
                $this->getTable(self::STORE_DATA_TABLE_NAME),
                $cookieStoreData
            );
            $this->getConnection()->commit();
            $cookie->afterSave();
        } else {
            parent::save($cookie);
        }

        return $this;
    }

    public function setStoreId(int $storeId)
    {
        $this->storeId = $storeId;
    }
}
