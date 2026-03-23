<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\ResourceModel\PolicyContent;

use Amasty\Gdpr\Model\PolicyContent;
use Amasty\Gdpr\Model\ResourceModel\PolicyContent as PolicyContentResource;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Psr\Log\LoggerInterface;

/**
 * @method PolicyContent[] getItems()
 */
class Collection extends AbstractCollection
{
    /**
     * @var CollectionFactory
     */
    private $factory;

    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        CollectionFactory $factory,
        ?AdapterInterface $connection = null,
        ?AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->factory = $factory;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _construct()
    {
        $this->_init(PolicyContent::class, PolicyContentResource::class);
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    /**
     * @param $policyId
     * @param $storeId
     *
     * @return PolicyContent
     */
    public function findByStoreAndPolicy($policyId, $storeId)
    {
        /** @var Collection $contentCollection */
        $contentCollection = $this->factory->create();

        /** @var PolicyContent $content */
        $content = $contentCollection
            ->addFieldToFilter('store_id', $storeId)
            ->addFieldToFilter('policy_id', $policyId)
            ->getFirstItem();

        return $content;
    }
}
