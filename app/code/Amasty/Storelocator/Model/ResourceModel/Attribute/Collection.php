<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Model\ResourceModel\Attribute;

use Amasty\Storelocator\Block\Adminhtml\Attribute\Edit\Tab\Main;
use Amasty\Storelocator\Model\AttributesProcessor;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Store\Model\StoreManagerInterface;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var \Amasty\Base\Model\Serializer
     */
    private $serializer;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var AttributesProcessor
     */
    private $attributesProcessor;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Amasty\Base\Model\Serializer $serializer,
        StoreManagerInterface $storeManager,
        ?AdapterInterface $connection = null,
        ?AbstractDb $resource = null,
        ?AttributesProcessor $attributesProcessor = null // TODO move to not optional
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->serializer = $serializer;
        $this->storeManager = $storeManager;
        $this->attributesProcessor = $attributesProcessor ?? ObjectManager::getInstance()->get(
            AttributesProcessor::class
        );
    }

    protected function _construct()
    {
        $this->_init(
            \Amasty\Storelocator\Model\Attribute::class,
            \Amasty\Storelocator\Model\ResourceModel\Attribute::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    public function joinAttributes($types)
    {
        $fromPart = $this->getSelect()->getPart(Select::FROM);
        if (!isset($fromPart['option'])) {
            $this->getSelect()
                ->joinLeft(
                    ['option' => $this->getTable('amasty_amlocator_attribute_option')],
                    'main_table.attribute_id = option.attribute_id',
                    [
                        'attribute_id'       => 'main_table.attribute_id',
                        'options_serialized' => 'option.options_serialized',
                        'value_id'           => 'option.value_id'
                    ]
                )
                ->order("option.sort_order");
            $this->addFieldToFilter('main_table.frontend_input', ['in' => $types]);
        }

        return $this;
    }

    public function getAttributes()
    {
        $connection = $this->getConnection();
        $select = $this->getSelect();

        return $connection->fetchAll($select);
    }

    public function preparedAttributes($allAttributes = false, $storeId = null)
    {
        if (!$storeId) {
            $storeId = $this->storeManager->getStore(true)->getId();
        }

        // remove text field from widget and filter
        $types = $allAttributes ? Main::ALLOWED_ATTRIBUTES : Main::FILTER_ATTRIBUTES;

        $attrAsArray = $this->joinAttributes($types)->getAttributes();

        return $this->attributesProcessor->process($attrAsArray, $storeId);
    }
}
