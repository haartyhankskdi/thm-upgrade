<?php
namespace Kdi\CustomExport\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\App\ResourceConnection;

class Transaction extends Column
{
    protected $resourceConnection;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        ResourceConnection $resourceConnection,
        array $components = [],
        array $data = []
    ) {
        $this->resourceConnection = $resourceConnection;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $transactionId = $this->getTransactionId($item['entity_id']);
                $item[$this->getData('name')] = $transactionId;
            }
        }

        return $dataSource;
    }

    public function getTransactionId($entityId)
    {
        $connection = $this->resourceConnection->getConnection();
        // $tableName = $this->resourceConnection->getTableName('magenest_sagepay_transaction');

        // $select = $connection->select()
        //     ->from($tableName, 'transaction_id')
        //     ->where('order_id = ?', $entityId);

        // return $connection->fetchOne($select);
    }
}
