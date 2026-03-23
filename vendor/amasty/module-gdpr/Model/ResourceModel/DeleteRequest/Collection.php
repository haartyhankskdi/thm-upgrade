<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\ResourceModel\DeleteRequest;

use Amasty\Gdpr\Api\Data\DeleteRequestInterface;
use Amasty\Gdpr\Model\DeleteRequest;
use Amasty\Gdpr\Model\ResourceModel\DeleteRequest as DeleteRequestResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _construct()
    {
        parent::_construct();

        $this->_init(DeleteRequest::class, DeleteRequestResource::class);
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    /**
     * @param $customerId
     */
    public function deleteByCustomerId($customerId)
    {
        $this->getConnection()->delete(
            $this->getMainTable(),
            ['customer_id = ?' => (int)$customerId]
        );
    }

    /**
     * @param $customerId
     */
    public function approveRequest($customerId)
    {
        $this->getConnection()->update(
            $this->getMainTable(),
            [DeleteRequestInterface::APPROVED => DeleteRequest::IS_APPROVED],
            ['customer_id = ?' => (int)$customerId]
        );
    }
}
