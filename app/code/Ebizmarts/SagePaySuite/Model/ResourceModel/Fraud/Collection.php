<?php

namespace Ebizmarts\SagePaySuite\Model\ResourceModel\Fraud;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;
use Magento\Sales\Model\ResourceModel\Order\Payment\Transaction;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Magento\Framework\DB\Select;

class Collection extends SearchResult
{
    private const JSON_FIELD = 'additional_information';

    /** @var string[] */
    private $fraudConditions;

    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = 'sales_payment_transaction',
        $resourceModel = Transaction::class,
        $fraudConditions = []
    ) {
        $this->fraudConditions = $fraudConditions;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }

    protected function _renderFiltersBefore()
    {
        $wherePart = $this->getSelect()->getPart(Select::WHERE);
        foreach ($wherePart as $key => $condition) {
            $filterConditionKey = $this->filterCondition($condition);
            if ($filterConditionKey) {
                $wherePart[$key] = str_replace(
                    '`'.$filterConditionKey.'`',
                    'JSON_EXTRACT(payments.'.self::JSON_FIELD.',"$.'.$this->fraudConditions[$filterConditionKey].'")',
                    $condition
                );
            }
            $this->getSelect()->setPart(Select::WHERE, $wherePart);
        }
        parent::_renderFiltersBefore();
    }

    protected function _initSelect()
    {
        parent::_initSelect();
        $this->addJsonExpressions();

        $this->getSelect()
            ->join(
                ['payments' => $this->getTable('sales_order_payment')],
                'payments.entity_id = main_table.payment_id'
            )->join(
                ['orders' => $this->getTable('sales_order')],
                'orders.entity_id = main_table.order_id'
            )->where("sagepaysuite_fraud_check=1");
        return $this;
    }

    /**
     * @return void
     */
    private function addJsonExpressions()
    {
        foreach ($this->fraudConditions as $key => $value) {
            $this->addExpressionFieldToSelect(
                $key,
                'JSON_UNQUOTE(JSON_EXTRACT(payments.'.self::JSON_FIELD.',"$.'.$value.'"))',
                ['payments.additional_information']
            );
        }
    }

    /**
     * @param string $condition
     * @return int|string|null
     */
    private function filterCondition($condition)
    {
        $filterCondition = null;
        foreach ($this->fraudConditions as $key => $value) {
            if (strpos($condition, $key) !== false) {
                $filterCondition = $key;
                break;
            }
        }
        return $filterCondition;
    }
}
