<?php

namespace Ebizmarts\SagePaySuite\Model;

use Ebizmarts\SagePaySuite\Api\Data\SyncOrderInterfaceFactory;
use Ebizmarts\SagePaySuite\Api\Data\SyncOrderInterface;
use Ebizmarts\SagePaySuite\Api\SyncFromApiModelInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Model\AbstractModel;

class SyncFromApi extends AbstractModel implements SyncFromApiModelInterface
{
    /** @var SyncOrderInterfaceFactory $syncOrderInterfaceFactory */
    private $syncOrderInterfaceFactory;
    /**
     * @param Context $context
     * @param Registry $registry
     * @param SyncOrderInterfaceFactory $syncOrderInterfaceFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        SyncOrderInterfaceFactory $syncOrderInterfaceFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->syncOrderInterfaceFactory = $syncOrderInterfaceFactory;
    }

    /**
     * Init model
     *
     * @return void
     */
    // @codingStandardsIgnoreStart
    protected function _construct()
    {
        $this->_init('Ebizmarts\SagePaySuite\Model\ResourceModel\SyncFromApi');
    }
    // @codingStandardsIgnoreEnd

    /**
     * @inheritdoc
     */
    public function saveSyncedOrder($syncOrderInterface)
    {
        if (empty($this->getByOrderId($syncOrderInterface->getOrderId()))) {
            $this->setOrderId($syncOrderInterface->getOrderId());
            $this->setStatusCode($syncOrderInterface->getStatusCode());
            $this->setStatusDetail($syncOrderInterface->getStatusDetail());
            $this->setSyncAttempts(1);
            $this->save();
            return $this;
        }
    }

    /**
     * @inheritdoc
     */
    public function updateTable($syncOrderInterface)
    {
        if ($syncOrderInterface === null) {
            return;
        }

        $this->setOrderId($syncOrderInterface->getOrderId());
        $this->setStatusCode($syncOrderInterface->getStatusCode());
        $this->setStatusDetail($syncOrderInterface->getStatusDetail());
        $this->setSyncAttempts($syncOrderInterface->getSyncAttempts()+1);
        $this->save();
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getById($id)
    {
        $order = $this->getResource()->getById($id);

        if ($order === null || $order == false) {
            return null;
        }

        $result = $this->toDto($order);
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getByOrderId($orderId)
    {
        $order = $this->getResource()->getByOrderId($orderId);

        if ($order === null || $order == false) {
            return null;
        }

        $this->setId($order['id'])
            ->setOrderId($order['order_id'])
            ->setStatusCode($order['status_code'])
            ->setStatusDetail($order['status_detail'])
            ->setSyncAttempts($order['sync_attempts']);

        return $this;
    }

    /**
     * @param array $syncedOrder
     * @return SyncOrderInterface
     */
    private function toDto($syncedOrder)
    {
        /** @var SyncOrderInterface $SyncOrderInterface */
        $SyncOrderInterface = $this->syncOrderInterfaceFactory->create();
        $SyncOrderInterface->setId($syncedOrder['id']);
        $SyncOrderInterface->setOrderId($syncedOrder[SyncOrderInterface::ORDER_ID]);
        $SyncOrderInterface->setStatusCode($syncedOrder[SyncOrderInterface::STATUS_CODE]);
        $SyncOrderInterface->setSyncAttempts($syncedOrder[SyncOrderInterface::SYNC_ATTEMPTS]);
        $SyncOrderInterface->setStatusDetail($syncedOrder[SyncOrderInterface::STATUS_DETAIL]);

        return $SyncOrderInterface;
    }
}
