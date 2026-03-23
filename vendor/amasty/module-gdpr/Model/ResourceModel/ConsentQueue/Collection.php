<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\ResourceModel\ConsentQueue;

use Amasty\Gdpr\Api\Data\ConsentQueueInterface;
use Amasty\Gdpr\Model\ConsentQueue as ConsentQueueModel;
use Amasty\Gdpr\Model\ResourceModel\ConsentQueue as ConsentQueueResource;
use Amasty\Gdpr\Model\VisitorConsentLog\ResourceModel\VisitorConsentLog;
use Amasty\Gdpr\Model\VisitorConsentLog\VisitorConsentLog as VisitorConsentLogModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    public function _construct()
    {
        $this->_init(ConsentQueueModel::class, ConsentQueueResource::class);
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    public function addCustomerIp(): Collection
    {
        $this->join(
            ['vcl' => $this->getTable(VisitorConsentLog::TABLE_NAME)],
            'main_table.' . ConsentQueueInterface::CUSTOMER_ID . ' = vcl.' . VisitorConsentLogModel::CUSTOMER_ID,
            ['ip']
        );

        return $this;
    }

    public function addCustomerStore(): Collection
    {
        $this->join(
            ['ce' => $this->getTable('customer_entity')],
            'main_table.' . ConsentQueueInterface::CUSTOMER_ID . ' = ce.entity_id',
            ['store_id']
        );

        return $this;
    }
}
