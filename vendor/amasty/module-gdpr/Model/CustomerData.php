<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model;

use Magento\Customer\Model\Data\Customer;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Eav\Api\Data\AttributeSearchResultsInterface;
use Magento\Eav\Model\AttributeRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Model\Order;

class CustomerData
{
    public const CUSTOMER_TABLE = 'customer_entity';

    /**
     * @var array
     */
    private $customerPersonalDataMapper = [
        'wishlist' => 'customer_wishlist',
        'salesrule_customer' => 'customer_salesrule',
        'downloadable_link_purchased' => 'downloadable_products',
        'report_viewed_product_index' => 'viewed_products',
        'review_detail' => 'product_reviews'
    ];

    /**
     * @var array
     */
    private $customerDataTables = [
        'catalog_compare_item',
        'catalog_product_frontend_action',
        'downloadable_link_purchased',
        'magento_customer_balance',
        'magento_customer_segment_customer',
        'magento_reward',
        'magento_rma',
        'oauth_token',
        'paypal_billing_agreement',
        'persistent_session',
        'product_alert_price',
        'product_stock_alert',
        'report_compared_product_index',
        'report_viewed_product_index',
        'review_detail',
        'salesrule_coupon_usage',
        'salesrule_customer',
        'wishlist'
    ];

    /**
     * @var string[][]
     */
    private $allowedAttributes = [
        'customer'         => [
            'prefix',
            'firstname',
            'middlename',
            'lastname',
            'suffix',

            'email',
            'dob',
            'gender',
            'taxvat'
        ],
        'customer_address' => [
            'prefix',
            'firstname',
            'middlename',
            'lastname',
            'suffix',

            'company',
            'street',
            'city',
            'country_id',
            'region',
            'region_id',
            'postcode',
            'telephone',
            'fax',
            'vat_id'
        ],
        'gift_registry_entity' => [
            'event_country',
            'event_country_region',
            'event_country_region_text',
            'event_location',
            'shipping_address',
            'custom_values',
            'event_date'
        ],
        'gift_registry_person' => [
            'firstname',
            'lastname',
            'email',
            'role',
            'custom_values'
        ],
        'exclude' => [
            'id',
            'group_id',
            'default_billing',
            'default_shipping',
            'created_at',
            'updated_at',
            'created_in',
            'store_id',
            'website_id',
            'disable_auto_group_change',
            'chop_enable',
            'is_subscribed',
            'confirmation'
        ]
    ];

    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * @var AttributeRepository
     */
    private $attributeRepository;

    /**
     * @var SearchCriteriaBuilderFactory
     */
    private $searchCriteriaBuilderFactory;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var OrderInterfaceFactory
     */
    private $orderFactory;

    /**
     * @var Config
     */
    private $configProvider;

    public function __construct(
        CustomerRepository $customerRepository,
        AttributeRepository $attributeRepository,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        ResourceConnection $resourceConnection,
        OrderInterfaceFactory $orderFactory,
        Config $configProvider
    ) {
        $this->customerRepository = $customerRepository;
        $this->attributeRepository = $attributeRepository;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
        $this->resourceConnection = $resourceConnection;
        $this->orderFactory = $orderFactory;
        $this->configProvider = $configProvider;
    }

    /**
     * Get array of attribute names by entity code
     *
     * @param string $type
     *
     * @return array
     */
    public function getAttributeCodes($type)
    {
        $attributeCodes = [];

        if (isset($this->allowedAttributes[$type])) {
            $attributeCodes = $this->allowedAttributes[$type];
        }

        return $attributeCodes;
    }

    public function getPersonalData(int $customerId, bool $mergeIntoOneFile): array
    {
        /** @var Customer $customer */
        $customer = $this->customerRepository->getById($customerId);
        $excludedFields = $this->configProvider->getExcludedFields();

        $data = array_merge(
            $this->getCustomerEavData($customer, $mergeIntoOneFile),
            $this->getAddressEavDataFromCustomer($customer, $mergeIntoOneFile),
            $this->getRelatedCustomerData($customerId, $mergeIntoOneFile)
        );

        if (!empty($excludedFields)) {
            if ($mergeIntoOneFile) {
                $data = $this->getFilteredDataArray($data, $excludedFields);
            } else {
                foreach ($data as &$dataitem) {
                    $dataitem = $this->getFilteredDataArray($dataitem, $excludedFields);
                }
            }
        }

        if ($this->configProvider->isSkipEmptyFields()) {
            if ($mergeIntoOneFile) {
                $data = $this->skipEmptyFields($data);
            } else {
                $filteredData = [];
                foreach ($data as $key => $part) {
                    $filteredValue = $this->skipEmptyFields($part);
                    if ($filteredValue) {
                        $filteredData[$key] = $filteredValue;
                    }
                }
                $data = $filteredData;
            }
        }

        return $data;
    }

    /**
     * @param string $incrementId
     *
     * @return array
     */
    public function getGuestPersonalData($incrementId, bool $mergeIntoOneFile): array
    {
        /** @var $order Order */
        $order = $this->orderFactory->create()->loadByIncrementId($incrementId);
        if (!$order->getId() || !$order->getCustomerIsGuest()) {
            return [];
        }

        $data = array_merge(
            $this->getCustomerEavDataFromOrder($order, $mergeIntoOneFile),
            $this->getAddressEavDataFromOrder($order, $mergeIntoOneFile)
        );

        return $data;
    }

    public function getAttributesInputType(array $attributeCodes = [], string $entityCode = 'customer'): array
    {
        $result = [];
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();
        if (!empty($attributeCodes)) {
            $searchCriteriaBuilder->addFilter('attribute_code', $attributeCodes, 'in');
        }

        $attributes = $this->attributeRepository->getList($entityCode, $searchCriteriaBuilder->create());
        /** @var \Magento\Customer\Model\Attribute $attribute */
        foreach ($attributes->getItems() as $attribute) {
            $result[$attribute->getAttributeCode()] = $attribute->getFrontendInput();
        }

        return $result;
    }

    /**
     * @param Order $order
     *
     * @return array
     */
    protected function getCustomerDataFromOrder(Order $order): array
    {
        $customerData = [];
        foreach ($order->toArray() as $key => $value) {
            $pos = strpos($key, 'customer_');
            if ($pos === 0) {
                $customerData[substr($key, strlen('customer_'))] = $value;
            }
        }

        return $customerData;
    }

    /**
     * @param Order $order
     * @param bool $mergeIntoOneFile
     *
     * @return array
     */
    protected function getCustomerEavDataFromOrder(Order $order, bool $mergeIntoOneFile): array
    {
        $customerData = $this->getCustomerDataFromOrder($order);
        $data = $this->collectAttributeValues($customerData, $this->getAttributes('customer'));

        return $mergeIntoOneFile ? $data : ['customer_information' => $data];
    }

    /**
     * @param Customer $customer
     * @param bool $mergeIntoOneFile
     *
     * @return array
     */
    protected function getCustomerEavData(Customer $customer, bool $mergeIntoOneFile)
    {
        $data = $this->collectAttributeValues($customer->__toArray(), $this->getAttributes('customer'));

        return $mergeIntoOneFile ? $data : ['customer_information' => $data];
    }

    /**
     * @param Customer $customer
     * @param bool $mergeIntoOneFile
     *
     * @return array
     */
    protected function getAddressEavDataFromCustomer(Customer $customer, bool $mergeIntoOneFile): array
    {
        $addresses = [];
        /** @var  $address \Magento\Customer\Model\Data\Address */
        foreach ($customer->getAddresses() as $address) {
            $addresses[] = $address->__toArray();
        }
        $data = $this->getAddressEavData($addresses);

        return $mergeIntoOneFile ? $data : ['customer_address' => $data];
    }

    /**
     * @param Order $order
     * @param bool $mergeIntoOneFile
     *
     * @return array
     */
    protected function getAddressEavDataFromOrder(Order $order, bool $mergeIntoOneFile): array
    {
        $addresses = [];
        /** @var $address \Magento\Sales\Model\Order\Address */
        foreach ($order->getAddresses() as $address) {
            $addresses[] = $address->toArray();
        }
        $data = $this->getAddressEavData($addresses);

        return $mergeIntoOneFile ? $data : ['customer_address' => $data];
    }

    /**
     * @param array $addresses
     *
     * @return array
     */
    protected function getAddressEavData(array $addresses)
    {
        $attributes = $this->getAttributes('customer_address');
        $result = [];
        $i = 0;

        foreach ($addresses as $address) {
            $i++;

            foreach ($this->collectAttributeValues($address, $attributes, "Address #$i ") as $item) {
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * @param array $data
     * @param SearchResultsInterface $attributes
     * @param string $namePrefix
     *
     * @return array
     */
    protected function collectAttributeValues(
        array $data,
        SearchResultsInterface $attributes,
        $namePrefix = ''
    ) {
        $result = [];

        /** @var \Magento\Customer\Model\Attribute $attribute */
        foreach ($attributes->getItems() as $attribute) {
            if (!isset($data[$attribute->getAttributeCode()])) {
                continue;
            }

            $value = $data[$attribute->getAttributeCode()];
            if (!empty($value)) {
                if (is_array($value) && $attribute->getAttributeCode() !== 'street') {
                    $value = reset($value);
                } elseif (is_array($value) && $attribute->getAttributeCode() === 'street') {
                    foreach ($value as $k => $v) {
                        $result [] = [
                            $namePrefix . $attribute->getStoreLabel() . ' ' . ($k+1),
                            $v
                        ];
                    }

                    continue;
                }
                $result [] = [
                    $namePrefix . $attribute->getStoreLabel(),
                    $value
                ];
            }
        }

        return $result;
    }

    /**
     * @param $entityCode
     *
     * @return AttributeSearchResultsInterface
     */
    protected function getAttributes($entityCode)
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();

        $searchCriteria = $searchCriteriaBuilder
            ->addFilter('attribute_code', $this->allowedAttributes[$entityCode], 'in')
            ->create();

        return $this->attributeRepository->getList($entityCode, $searchCriteria);
    }

    protected function getRelatedCustomerData(int $customerId, bool $mergeIntoOneFile): array
    {
        $preparedCustomerData = [];
        $connection = $this->resourceConnection->getConnection();
        $customerTable = $this->resourceConnection->getTableName(self::CUSTOMER_TABLE);
        $select = $connection->select()
            ->from($customerTable, [])
            ->where($customerTable . '.entity_id = ?', $customerId)
            ->group($customerTable . '.entity_id');

        foreach ($this->customerDataTables as $table) {
            $tableName = $this->resourceConnection->getTableName($table);

            if ($connection->isTableExists($tableName)
                && $connection->tableColumnExists($tableName, 'customer_id')
            ) {
                $select->joinLeft(
                    $tableName,
                    $customerTable . '.entity_id = ' . $tableName . '.customer_id',
                    $this->getColumnsForSelect($tableName, $table)
                );
            }
        }

        $customerData = $connection->fetchRow($select);

        foreach ($customerData as $key => $value) {
            if ($mergeIntoOneFile) {
                $preparedCustomerData[] = [$key, $value];
            } else {
                $entityName = explode('.', $key);
                $groupName = $this->customerPersonalDataMapper[$entityName[0]] ?? $entityName[0];
                $preparedCustomerData[$groupName][] = [$entityName[1], $value];
            }
        }

        return $preparedCustomerData;
    }

    private function getColumnsForSelect(string $tableName, string $alias): array
    {
        $columns = [];
        $tableColumns = $this->resourceConnection->getConnection()->describeTable($tableName);

        foreach ($tableColumns as $column => $config) {
            $columns["{$alias}.{$column}"] = new \Zend_Db_Expr(
                sprintf(
                    'GROUP_CONCAT(%s.%s)',
                    $tableName,
                    $column
                )
            );
        }

        return $columns;
    }

    /**
     * @param array $data
     * @return array
     */
    private function skipEmptyFields(array $data): array
    {
        return array_filter($data, function ($dataPair) {
            $dataValue = $dataPair[1] ?? null;

            return !in_array($dataValue, ['', false, null], true);
        });
    }

    /**
     * @param array $data
     * @param array $excludedFields
     * @return array
     */
    private function getFilteredDataArray(array $data, array $excludedFields): array
    {
        return array_filter($data, function ($dataPair) use ($excludedFields) {
            $dataValue = $dataPair[0] ?? null;

            return !in_array($dataValue, $excludedFields, true);
        });
    }
}
