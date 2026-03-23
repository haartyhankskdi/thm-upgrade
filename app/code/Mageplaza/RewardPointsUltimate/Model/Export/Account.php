<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_RewardPointsUltimate
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsUltimate\Model\Export;

use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\ImportExport\Model\Export;
use Magento\ImportExport\Model\Export\AbstractEntity;
use Magento\ImportExport\Model\Export\Factory;
use Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\RewardPoints\Model\ResourceModel\Account\Collection;

/**
 * Class Account
 * @package Mageplaza\RewardPointsUltimate\Model\Export
 */
class Account extends AbstractEntity
{

    /**
     * Permanent entity columns
     *
     * @var string[]
     */
    protected $_permanentAttributes
        = [
            AttributeCollection::CUSTOMER_EMAIL,
            AttributeCollection::POINT_BALANCE,
            AttributeCollection::STORE_ID,
            AttributeCollection::WEBSITE_ID,
            AttributeCollection::NOTIFICATION_UPDATE,
            AttributeCollection::NOTIFICATION_EXPIRE,
            AttributeCollection::IS_ACTIVE
        ];

    /**
     * Attribute collection name
     */
    const ATTRIBUTE_COLLECTION_NAME = AttributeCollection::class;

    /**
     * @var Collection
     */
    protected $rewardAccountCollection;

    /**
     * Account constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param Factory $collectionFactory
     * @param CollectionByPagesIteratorFactory $resourceColFactory
     * @param Collection $rewardAccountCollection
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        Factory $collectionFactory,
        CollectionByPagesIteratorFactory $resourceColFactory,
        Collection $rewardAccountCollection,
        array $data = []
    ) {
        $this->rewardAccountCollection = $rewardAccountCollection;
        parent::__construct($scopeConfig, $storeManager, $collectionFactory, $resourceColFactory, $data);
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function export()
    {
        $filters = $this->_parameters;
        $columns = $this->_getHeaderColumns();
        $collection = $this->_getEntityCollection();
        $exportFilter = $filters[Export::FILTER_ELEMENT_GROUP];
        foreach ($exportFilter as $field => $filter) {
            if (in_array($field, $columns, true)) {
                if (is_array($filter)) {
                    if ($filter[0]) {
                        $collection->addFieldToFilter($field, ['from' => $filter[0]]);
                    }

                    if ($filter[1]) {
                        $collection->addFieldToFilter($field, ['to' => $filter[1]]);
                    }
                } elseif ($field === AttributeCollection::CUSTOMER_EMAIL) {
                    $collection->addFieldToFilter($field, [
                        'like' => "%{$filter}%"
                    ]);
                } elseif ($filter || $filter === '0') {
                    $collection->addFieldToFilter($field, $filter);
                }
            }
        }

        $this->getWriter()->setHeaderCols($columns);

        $this->_exportCollectionByPages($collection);

        return $this->getWriter()->getContents();
    }

    /**
     * @param AbstractModel $item
     *
     * @throws LocalizedException
     */
    public function exportItem($item)
    {
        $this->getWriter()->writeRow($item->getData());
    }

    /**
     * @return string
     */
    public function getEntityTypeCode()
    {
        return 'mp_reward_account';
    }

    /**
     * @return array|string[]
     * @throws LocalizedException
     */
    protected function _getHeaderColumns()
    {
        $filters = $this->_parameters;
        if (!isset($filters[Export::FILTER_ELEMENT_SKIP])) {
            return $this->_permanentAttributes;
        }

        $skip = $filters[Export::FILTER_ELEMENT_SKIP];

        if (count($skip) === count($this->_permanentAttributes)) {
            throw new LocalizedException(__('There is no data for the export.'));
        }

        return array_diff($this->_permanentAttributes, $skip);
    }

    /**
     * @return AbstractDb|Collection
     */
    protected function _getEntityCollection()
    {
        $accountCollection = $this->rewardAccountCollection;
        $accountCollection->getSelect()->join(
            ['customer' => $accountCollection->getTable('customer_entity')],
            'main_table.customer_id = customer.entity_id',
            ['email', 'website_id', 'store_id']
        );
        $accountCollection->addFilterToMap('website_id', 'customer.website_id');
        $accountCollection->addFilterToMap('email', 'customer.email');
        $accountCollection->addFilterToMap('is_active', 'main_table.is_active');
        $accountCollection->addFilterToMap('store_id', 'customer.store_id');

        return $accountCollection;
    }

    /**
     * @return \Magento\Framework\Data\Collection|mixed
     * @throws Exception
     */
    public function getAttributeCollection()
    {
        return $this->_attributeCollection->buildAttributes($this->_permanentAttributes);
    }
}
