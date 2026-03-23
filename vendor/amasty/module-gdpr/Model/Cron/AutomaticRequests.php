<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\Cron;

use Amasty\Gdpr\Api\Data\DeleteRequestInterface;
use Amasty\Gdpr\Api\DeleteRequestRepositoryInterface;
use Amasty\Gdpr\Model\CleaningDate;
use Amasty\Gdpr\Model\DeleteRequest\DeleteRequestSource;
use Amasty\Gdpr\Model\DeleteRequestFactory;
use Amasty\Gdpr\Model\ResourceModel\DeleteRequest\CollectionFactory as DeleteRequestCollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Sales\Api\Data\OrderInterface;

class AutomaticRequests
{
    /**
     * @var CleaningDate
     */
    private $cleaningDate;

    /**
     * @var DeleteRequestRepositoryInterface
     */
    private $deleteRequestRepository;

    /**
     * @var DeleteRequestFactory
     */
    private $deleteRequestFactory;

    /**
     * @var DeleteRequestCollectionFactory
     */
    private $deleteRequestCollectionFactory;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(
        CleaningDate $cleaningDate,
        DeleteRequestRepositoryInterface $deleteRequestRepository,
        DeleteRequestFactory $deleteRequestFactory,
        DeleteRequestCollectionFactory $deleteRequestCollectionFactory,
        ResourceConnection $resourceConnection
    ) {
        $this->cleaningDate = $cleaningDate;
        $this->deleteRequestRepository = $deleteRequestRepository;
        $this->deleteRequestFactory = $deleteRequestFactory;
        $this->deleteRequestCollectionFactory = $deleteRequestCollectionFactory;
        $this->resourceConnection = $resourceConnection;
    }

    public function orderProcess()
    {
        if (!$dateForRemove = $this->cleaningDate->getPersonalDataDeletionDate()) {
            return;
        }

        $alreadyDeletedCustomers = $this->deleteRequestCollectionFactory->create()
            ->addFieldToSelect(DeleteRequestInterface::CUSTOMER_ID)
            ->getData();

        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from(['ce' => $this->resourceConnection->getTableName('customer_entity')])
            ->reset(Select::COLUMNS)
            ->columns(['ce.entity_id'])
            ->joinLeft(
                ['so' => $this->resourceConnection->getTableName('sales_order')],
                'ce.entity_id = so.customer_id',
                ['MAX(so.created_at) as lastOrderDate']
            )
            ->where('ce.created_at <= :dateForRemove')
            ->having('lastOrderDate <= :dateForRemove OR lastOrderDate IS NULL')
            ->group('ce.entity_id');

        if ($alreadyDeletedCustomers) {
            $select->where('ce.entity_id NOT IN (?)', $alreadyDeletedCustomers);
        }

        $customerForDeletion = $connection->fetchCol($select, [':dateForRemove' => $dateForRemove]);
        foreach ($customerForDeletion as $customerId) {
            $request = $this->deleteRequestFactory->create();
            $request->setCustomerId($customerId);
            $request->setGotFrom(DeleteRequestSource::AUTOMATIC);
            $this->deleteRequestRepository->save($request);
        }
    }
}
