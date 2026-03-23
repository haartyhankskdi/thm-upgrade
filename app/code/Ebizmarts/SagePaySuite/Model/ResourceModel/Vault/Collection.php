<?php

namespace Ebizmarts\SagePaySuite\Model\ResourceModel\Vault;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;
use Magento\Vault\Model\ResourceModel\PaymentToken;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

class Collection extends SearchResult
{
    private $conditions = [
        'expiration_date' => 'expirationDate',
        'cc_type' => 'type',
        'cc_last_4' => 'maskedCC'
        ];
    private const JSON_FIELD = 'details';

    // @codingStandardsIgnoreStart
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = 'vault_payment_token',
        $resourceModel = PaymentToken::class
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }
    // @codingStandardsIgnoreEnd
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->addJsonExpressions();
        $this->getSelect()
            ->joinLeft(
                ['customers' => $this->getTable('customer_entity')],
                'customers.entity_id = main_table.customer_id',
                "email"
            )
            ->where("main_table.is_active=1 and main_table.is_visible=1 and payment_method_code like 'sagepay%'");
        $this->addFilterToMap('created_at', 'main_table.created_at');
        return $this;
    }
    private function addJsonExpressions()
    {
        foreach ($this->conditions as $key => $value) {
            $this->addExpressionFieldToSelect(
                $key,
                'JSON_UNQUOTE(JSON_EXTRACT('.self::JSON_FIELD.',"$.'.$value.'"))',
                ['main_table.'.self::JSON_FIELD]
            );
        }
    }
    protected function _renderFiltersBefore()
    {
        $wherePart = $this->getSelect()->getPart(Select::WHERE);
        foreach ($wherePart as $key => $condition) {
            $toReplace = $this->filterCondition($condition);
            if ($toReplace) {
                $wherePart[$key] = str_replace(
                    '`'.$toReplace.'`',
                    'JSON_EXTRACT('.self::JSON_FIELD.',"$.'.$this->conditions[$toReplace].'")',
                    $condition
                );
            }
            $this->getSelect()->setPart(Select::WHERE, $wherePart);
        }
        parent::_renderFiltersBefore();
    }
    private function filterCondition($condition)
    {
        foreach ($this->conditions as $key => $value) {
            if (strpos($condition, $key)!==false) {
                return $key;
            }
        }
        return null;
    }
}
