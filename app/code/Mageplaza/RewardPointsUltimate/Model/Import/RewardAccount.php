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

namespace Mageplaza\RewardPointsUltimate\Model\Import;

use Exception;
use Magento\Framework\Validator\Exception as ValidatorException;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\StringUtils;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\AbstractEntity;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\ImportFactory;
use Magento\ImportExport\Model\ResourceModel\Helper;
use Mageplaza\RewardPoints\Helper\Email as HelperEmail;
use Mageplaza\RewardPoints\Model\AccountFactory;
use Mageplaza\RewardPoints\Model\ResourceModel\Account as ResourceRewardAccount;
use Mageplaza\RewardPoints\Model\TransactionFactory;
use Mageplaza\RewardPointsUltimate\Helper\Data;

/**
 * Class RewardAccount
 * @package Mageplaza\RewardPointsUltimate\Model\Import
 */
class RewardAccount extends AbstractEntity
{
    /**
     * columns
     */
    const REWARD_ID           = 'reward_id';
    const CUSTOMER_ID         = 'customer_id';
    const CUSTOMER_EMAIL      = 'customer_email';
    const POINT_BALANCE       = 'point_balance';
    const STORE_ID            = 'store_id';
    const WEBSITE_ID          = 'website_id';
    const NOTIFICATION_UPDATE = 'notification_update';
    const NOTIFICATION_EXPIRE = 'notification_expire';
    const IS_ACTIVE           = 'is_active';

    /**#@+
     * Error codes
     */
    const ERROR_WEBSITE_IS_EMPTY          = 'websiteIsEmpty';
    const ERROR_EMAIL_IS_EMPTY            = 'emailIsEmpty';
    const ERROR_INVALID_WEBSITE           = 'invalidWebsite';
    const ERROR_INVALID_EMAIL             = 'invalidEmail';
    const ERROR_REWARD_CUSTOMER_NOT_FOUND = 'rewardCustomerNotFound';
    const ERROR_REWARD_DISABLE            = 'rewardDisable';

    /** @inheritdoc */
    protected $masterAttributeCode = 'customer_email';

    /**
     * Permanent entity columns.
     *
     * @var string[]
     */
    protected $_permanentAttributes
        = [
            self::CUSTOMER_EMAIL,
            self::STORE_ID,
            self::WEBSITE_ID,
            self::POINT_BALANCE
        ];

    /** @inheritdoc */
    protected $_availableBehaviors
        = [
            Import::BEHAVIOR_APPEND,
            Import::BEHAVIOR_REPLACE,
            Import::BEHAVIOR_DELETE
        ];

    /**
     * Valid column names
     *
     * @array
     */
    protected $validColumnNames
        = [
            self::CUSTOMER_EMAIL,
            self::POINT_BALANCE,
            self::STORE_ID,
            self::WEBSITE_ID
        ];

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var AccountManagementInterface
     */
    protected $customerManagement;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var CustomerInterfaceFactory
     */
    protected $customerDataFactory;

    /**
     * @var GroupManagementInterface
     */
    protected $groupManagementInterface;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepositoryInterface;

    /**
     * @var AccountFactory
     */
    protected $accountFactory;

    /**
     * @var HelperEmail
     */
    protected $helperEmail;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var ResourceRewardAccount
     */
    protected $resourceAccount;

    /**
     * @var array
     */
    protected $entityList = [];

    /**
     * @var array
     */
    protected $entityListTransaction = [];

    /**
     * @var null
     */
    protected $stores;

    /**
     * @var null
     */
    protected $websites;

    /**
     * @var array
     */
    protected $ids = [];

    /**
     * @var TransactionFactory
     */
    protected $transactionFactory;

    /**
     * RewardAccount constructor.
     *
     * @param StringUtils $string
     * @param ScopeConfigInterface $scopeConfig
     * @param ImportFactory $importFactory
     * @param Helper $resourceHelper
     * @param ResourceConnection $resource
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @param Data $helperData
     * @param AccountManagementInterface $accountManagement
     * @param CustomerInterfaceFactory $customerDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param GroupManagementInterface $groupManagementInterface
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param AccountFactory $accountFactory
     * @param HelperEmail $helperEmail
     * @param CustomerFactory $customerFactory
     * @param ResourceRewardAccount $resourceAccount
     * @param TransactionFactory $transactionFactory
     * @param array $data
     */
    public function __construct(
        StringUtils $string,
        ScopeConfigInterface $scopeConfig,
        ImportFactory $importFactory,
        Helper $resourceHelper,
        ResourceConnection $resource,
        ProcessingErrorAggregatorInterface $errorAggregator,
        Data $helperData,
        AccountManagementInterface $accountManagement,
        CustomerInterfaceFactory $customerDataFactory,
        DataObjectHelper $dataObjectHelper,
        GroupManagementInterface $groupManagementInterface,
        CustomerRepositoryInterface $customerRepositoryInterface,
        AccountFactory $accountFactory,
        HelperEmail $helperEmail,
        CustomerFactory $customerFactory,
        ResourceRewardAccount $resourceAccount,
        TransactionFactory $transactionFactory,
        array $data = []
    ) {
        $this->helperData                  = $helperData;
        $this->customerManagement          = $accountManagement;
        $this->customerDataFactory         = $customerDataFactory;
        $this->dataObjectHelper            = $dataObjectHelper;
        $this->groupManagementInterface    = $groupManagementInterface;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->accountFactory              = $accountFactory;
        $this->helperEmail                 = $helperEmail;
        $this->customerFactory             = $customerFactory;
        $this->resourceAccount             = $resourceAccount;
        $this->transactionFactory          = $transactionFactory;

        parent::__construct($string, $scopeConfig, $importFactory, $resourceHelper, $resource, $errorAggregator, $data);

        $this->addMessageTemplate(self::ERROR_WEBSITE_IS_EMPTY, __('Please specify a website.'));
        $this->addMessageTemplate(
            self::ERROR_EMAIL_IS_EMPTY,
            __("An email wasn't specified. Enter the email and try again.")
        );
        $this->addMessageTemplate(self::ERROR_INVALID_WEBSITE, __('We found an invalid value in a website column.'));
        $this->addMessageTemplate(self::ERROR_INVALID_EMAIL, __('Please enter a valid email.'));
        $this->addMessageTemplate(
            self::ERROR_REWARD_CUSTOMER_NOT_FOUND,
            __('We can\'t find a reward customer who matches this email and website code.')
        );
        $this->addMessageTemplate(
            self::ERROR_REWARD_DISABLE,
            __('Module has been disabled. Please enable the module to use this feature')
        );
    }

    /**
     * @return ProcessingErrorAggregatorInterface
     * @throws LocalizedException
     */
    public function validateData()
    {
        if ($this->getBehavior() === Import::BEHAVIOR_DELETE) {
            $this->_permanentAttributes = [
                self::CUSTOMER_EMAIL,
                self::WEBSITE_ID,
            ];
        }

        if ($this->getBehavior() === Import::BEHAVIOR_REPLACE) {
            $this->_permanentAttributes = [
                self::CUSTOMER_EMAIL,
                self::WEBSITE_ID,
                self::POINT_BALANCE
            ];
        }

        return parent::validateData();
    }

    /**
     * Entity type code getter.
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return 'mp_reward_account';
    }

    /**
     * @param array $rowData
     * @param $rowNumber
     *
     * throws ValidatorException
     */
    protected function _validateRowForDelete(array $rowData, $rowNumber)
    {
        if ($this->_checkUniqueKey($rowData, $rowNumber)) {
            if (!$this->getRewardAccountByCustomer($rowData[self::CUSTOMER_EMAIL], $rowData[self::WEBSITE_ID])) {
                $this->addRowError(self::ERROR_REWARD_CUSTOMER_NOT_FOUND, $rowNumber);
            }
        }
    }

    /**
     * @param string $email
     * @param string $websiteId
     *
     * @return mixed
     */
    public function getRewardAccountByCustomer($email, $websiteId)
    {
        $accountCollection = $this->accountFactory->create()->getCollection();
        $accountCollection->getSelect()->join(
            ['customer' => $accountCollection->getTable('customer_entity')],
            'main_table.customer_id = customer.entity_id',
            ['email', 'website_id']
        );
        $accountCollection->addFilterToMap('email', 'customer.email');
        $accountCollection->addFilterToMap('website_id', 'customer.website_id');
        $accountCollection->addFieldToFilter('email', $email);
        $accountCollection->addFieldToFilter('website_id', $websiteId);
        $id                                                             = $accountCollection->getFirstItem()->getId();
        $this->ids[$accountCollection->getFirstItem()->getCustomerId()] = $id;

        return $id;
    }

    /**
     * @param array $rowData
     * @param $rowNumber
     *
     * @return bool
     * @throws ValidatorException
     */
    protected function _checkUniqueKey(array $rowData, $rowNumber)
    {
        if (empty($rowData[self::WEBSITE_ID])) {
            $this->addRowError(self::ERROR_WEBSITE_IS_EMPTY, $rowNumber, self::WEBSITE_ID);
        } elseif (empty($rowData[self::CUSTOMER_EMAIL])) {
            $this->addRowError(self::ERROR_EMAIL_IS_EMPTY, $rowNumber, self::CUSTOMER_EMAIL);
        } else {
            $email   = $rowData[self::CUSTOMER_EMAIL];
            $website = $rowData[self::WEBSITE_ID];

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->addRowError(self::ERROR_INVALID_EMAIL, $rowNumber, self::CUSTOMER_EMAIL);
            } elseif (!isset($this->websites[$website])) {
                $this->addRowError(self::ERROR_INVALID_WEBSITE, $rowNumber, self::WEBSITE_ID);
            }
        }

        return !$this->getErrorAggregator()->isRowInvalid($rowNumber);
    }

    /**
     * @param array $rowData
     * @param int $rowNum
     *
     * @return bool
     * @throws LocalizedException
     * @throws ValidatorException
     */
    public function validateRow(array $rowData, $rowNum)
    {
        if (!$this->websites) {
            $this->websites = $this->helperData->getWebsites();
        }

        $this->_validatedRows[$rowNum] = true;
        if (Import::BEHAVIOR_DELETE === $this->getBehavior()) {
            $this->_validateRowForDelete($rowData, $rowNum);
        }

        if (Import::BEHAVIOR_REPLACE === $this->getBehavior()) {
            $this->_checkUniqueKey($rowData, $rowNum);
            $this->validateOptionalField($rowData, $rowNum);

            $customer = $this->customerFactory->create();
            $customer->setWebsiteId($rowData[self::WEBSITE_ID]);
            $customer->loadByEmail($rowData[self::CUSTOMER_EMAIL]);
            $account = $this->accountFactory->create();
            if (!$account->load($customer->getId(), 'customer_id')->getId()) {
                $this->addRowError(__('Reward Account invalid.'), $rowNum);
            }

            if (empty($rowData[self::POINT_BALANCE])) {
                $this->addRowError(__('Point balance is empty'), $rowNum);
            } elseif ($rowData[self::POINT_BALANCE] < 0) {
                $this->addRowError(__('Point balance must equals or greater than zero'), $rowNum);
            }

            $entity                                  = [];
            $entityTransactions                      = [];
            $entity[self::REWARD_ID]                 = $account->getId();
            $entity[self::CUSTOMER_ID]               = $customer->getId();
            $entity[self::POINT_BALANCE]             = 0;
            $entityTransactions[self::POINT_BALANCE] = $rowData[self::POINT_BALANCE];
            $entityTransactions[self::STORE_ID]      = $rowData[self::STORE_ID];
            $entityTransactions[self::WEBSITE_ID]    = $rowData[self::WEBSITE_ID];

            if (isset($rowData[self::NOTIFICATION_EXPIRE])) {
                $entity[self::NOTIFICATION_EXPIRE] = $rowData[self::NOTIFICATION_EXPIRE];
            }

            if (isset($rowData[self::NOTIFICATION_UPDATE])) {
                $entity[self::NOTIFICATION_UPDATE] = $rowData[self::NOTIFICATION_UPDATE];
            }

            if (isset($rowData[self::IS_ACTIVE])) {
                $entity[self::IS_ACTIVE] = $rowData[self::IS_ACTIVE];
            }

            $this->entityList[]            = $entity;
            $this->entityListTransaction[] = $entityTransactions;
        }

        if (Import::BEHAVIOR_APPEND === $this->getBehavior()) {
            $this->_checkUniqueKey($rowData, $rowNum);

            if (!$this->stores) {
                $this->stores = $this->helperData->getStores();
            }

            if (empty($rowData[self::POINT_BALANCE])) {
                $this->addRowError(__('Point balance is empty'), $rowNum);
            } elseif ($rowData[self::POINT_BALANCE] < 0) {
                $this->addRowError(__('Point balance must equals or greater than zero'), $rowNum);
            }

            if (empty($rowData[self::STORE_ID])) {
                $this->addRowError(__('Store id is empty'), $rowNum);
            } elseif (!isset($this->stores[$rowData[self::STORE_ID]])) {
                $this->addRowError(__('store_id doesn\'t exist'), $rowNum);
            }

            $this->validateOptionalField($rowData, $rowNum);
        }

        return !$this->getErrorAggregator()->isRowInvalid($rowNum);
    }

    /**
     * @param array $rowData
     * @param int $rowNum
     */
    protected function validateOptionalField($rowData, $rowNum)
    {
        if (isset($rowData[self::NOTIFICATION_EXPIRE]) &&
            !in_array($rowData[self::NOTIFICATION_EXPIRE], [0, 1, '0', '1'], true)) {
            $this->addRowError(__('%1 invalid', self::NOTIFICATION_EXPIRE), $rowNum);
        }

        if (isset($rowData[self::NOTIFICATION_UPDATE]) &&
            !in_array($rowData[self::NOTIFICATION_UPDATE], [0, 1, '0', '1'], true)) {
            $this->addRowError(__('%1 invalid', self::NOTIFICATION_UPDATE), $rowNum);
        }

        if (isset($rowData[self::IS_ACTIVE]) &&
            !in_array($rowData[self::IS_ACTIVE], [0, 1, '0', '1'], true)) {
            $this->addRowError(__('%1 invalid', self::IS_ACTIVE), $rowNum);
        }
    }

    /**
     * @return bool
     * @throws Exception
     */
    protected function _importData()
    {
        $connection = $this->_connection;
        if (!$this->helperData->isEnabled()) {
            $errorAggregator = $this->getErrorAggregator();
            $errorAggregator->addError(
                self::ERROR_REWARD_DISABLE
            );
            $connection->rollBack();
        }

        switch ($this->getBehavior()) {
            case Import::BEHAVIOR_DELETE:
                $this->deleteEntity();
                break;
            case Import::BEHAVIOR_REPLACE:
                $this->replaceEntity($connection);
                break;
            case Import::BEHAVIOR_APPEND:
                $this->saveEntity();
                break;
        }

        return true;
    }

    /**
     * @param $connection
     * @return $this
     * @throws LocalizedException
     * @throws ValidatorException
     */
    public function replaceEntity($connection)
    {
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach ($bunch as $rowNum => $rowData) {
                if (!$this->validateRow($rowData, $rowNum)) {
                    continue;
                }

                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                    continue;
                }
            }
        }

        if ($this->entityList) {
            $connection->beginTransaction();
            try {
                $this->setCancelAllTransactions($connection);
                $connection->insertOnDuplicate(
                    $this->resourceAccount->getMainTable(),
                    $this->entityList,
                    [
                        self::REWARD_ID,
                        self::CUSTOMER_ID,
                        self::POINT_BALANCE,
                        self::NOTIFICATION_UPDATE,
                        self::NOTIFICATION_EXPIRE,
                        self::IS_ACTIVE
                    ]
                );
                $connection->commit();

                foreach ($this->entityList as $key => $item) {
                    $customer    = $this->customerFactory->create()->load($item['customer_id']);
                    $transaction = $this->transactionFactory->create();
                    $transaction->createTransaction(
                        \Mageplaza\RewardPoints\Helper\Data::ACTION_ADMIN,
                        $customer,
                        new DataObject([
                            'point_amount'   => $this->entityListTransaction[$key][self::POINT_BALANCE],
                            'comment'        => 'Import',
                            self::STORE_ID   => $this->entityListTransaction[$key][self::STORE_ID],
                            self::WEBSITE_ID => $this->entityListTransaction[$key][self::WEBSITE_ID]
                        ])
                    );
                    $this->helperData->updateTier($customer->getId());
                }
            } catch (Exception $e) {
                $this->addException($e);
                $connection->rollBack();
            }

            $this->updateItemsCounterStats([], $this->entityList);
        }

        return $this;
    }

    /**
     * @param AdapterInterface $connection
     */
    public function setCancelAllTransactions($connection)
    {
        $transactionTable = $this->resourceAccount->getTable('mageplaza_reward_transaction');
        foreach ($this->entityList as $item) {
            if (isset($item['reward_id'])) {
                $selectTrans = $connection->select()
                    ->from($transactionTable, ['transaction_id', 'customer_id', 'status'])
                    ->where('reward_id = ?', $item['reward_id']);

                foreach ($connection->fetchAll($selectTrans) as $transaction) {
                    $connection->update(
                        $transactionTable,
                        ['status' => '3'],
                        ['transaction_id = ?' => $transaction['transaction_id']]
                    );
                }
            }
        }
    }

    /**
     * @return $this
     * @throws Exception
     */
    public function saveEntity()
    {
        $entityList = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach ($bunch as $rowNum => $rowData) {
                if (!$this->validateRow($rowData, $rowNum)) {
                    continue;
                }

                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                    continue;
                }

                foreach ($this->_permanentAttributes as $field) {
                    $entityList[$rowNum][$field] = $rowData[$field];
                }
                $entityList[$rowNum][self::POINT_BALANCE] = 0;
                if (isset($rowData[self::POINT_BALANCE])) {
                    $entityList[$rowNum][self::POINT_BALANCE] = $rowData[self::POINT_BALANCE];
                }

                if (isset($data[self::NOTIFICATION_EXPIRE])) {
                    $entityList[$rowNum][self::NOTIFICATION_EXPIRE] = $rowData[self::NOTIFICATION_EXPIRE];
                }

                if (isset($data[self::NOTIFICATION_UPDATE])) {
                    $entityList[$rowNum][self::NOTIFICATION_UPDATE] = $rowData[self::NOTIFICATION_UPDATE];
                }

                if (isset($data[self::IS_ACTIVE])) {
                    $entityList[$rowNum][self::IS_ACTIVE] = $rowData[self::IS_ACTIVE];
                }
            }
        }

        $this->saveEntityFinish($entityList);

        return $this;
    }

    /**
     * @param $data
     *
     * @return CustomerInterface
     * @throws LocalizedException
     */
    public function createCustomer($data)
    {
        $customer = $this->customerDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $customer,
            [
                'email'              => $data['customer_email'],
                'website_id'         => $data['website_id'],
                'firstname'          => 'firstname',
                'lastname'           => 'lastname',
                'group'              => $this->groupManagementInterface->getDefaultGroup($data['store_id']),
                'sendemail_store_id' => $data['store_id']
            ],
            CustomerInterface::class
        );

        return $this->customerManagement->createAccount($customer);
    }

    /**
     * @param array $entityData
     *
     * @return $this
     * @throws Exception
     */
    public function saveEntityFinish(array $entityData)
    {
        $connection = $this->_connection;
        $connection->beginTransaction();
        $update = [];
        $create = [];
        try {
            foreach ($entityData as $key => $data) {
                $customer = $this->customerFactory->create();
                $customer->setWebsiteId($data['website_id']);
                $customer->loadByEmail($data['customer_email']);
                $this->helperData->setActionImport(false);
                if (!$customer->getEntityId()) {
                    $this->helperData->setActionImport(true);
                    $customer = $this->createCustomer($data);
                }

                $account             = $this->accountFactory->create();
                $data['customer_id'] = $customer->getId();
                if ($account->load($customer->getId(), 'customer_id')->getId()) {
                    $data['point_balance'] = abs($data[self::POINT_BALANCE]);
                    $update[]              = $data;
                } else {
                    $create[]         = $data;
                    $subscribeDefault = $this->helperEmail->getEmailConfig(
                        'subscribe_by_default',
                        $customer->getStoreId()
                    );
                    if (!isset($data[self::NOTIFICATION_EXPIRE])) {
                        $data[self::NOTIFICATION_EXPIRE] = $subscribeDefault;
                    }

                    if (!isset($data[self::NOTIFICATION_UPDATE])) {
                        $data[self::NOTIFICATION_UPDATE] = $subscribeDefault;
                    }

                    if (!isset($data[self::IS_ACTIVE])) {
                        $data[self::IS_ACTIVE] = 1;
                    }
                }
                $transaction = $this->transactionFactory->create();
                $transaction->createTransaction(
                    \Mageplaza\RewardPoints\Helper\Data::ACTION_ADMIN,
                    $customer,
                    new DataObject([
                        'point_amount' => $data['point_balance'],
                        'comment'      => 'Import',
                        'store_id'     => $data['store_id'],
                        'website_id'   => $data['website_id']
                    ])
                );

                $this->helperData->updateTier($customer->getId());
            }

            $connection->commit();
        } catch (Exception $e) {
            $this->addException($e);

            $connection->rollBack();
        }

        $this->updateItemsCounterStats($create, $update);

        return $this;
    }

    /**
     * @return $this
     * @throws LocalizedException
     * @throws ValidatorException
     */
    public function deleteEntity()
    {
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach ($bunch as $rowNum => $rowData) {
                $this->validateRow($rowData, $rowNum);

                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                }
            }
        }

        if ($this->ids) {
            $this->updateItemsCounterStats([], [], $this->ids);
        }

        $condition = $this->_connection->quoteInto('reward_id IN (?)', $this->ids);
        $this->_connection->delete($this->resourceAccount->getMainTable(), $condition);
        $condition = $this->_connection->quoteInto('customer_id IN (?)', array_keys($this->ids));
        $this->_connection->update(
            $this->resourceAccount->getTable('mageplaza_reward_milestone_customer'),
            ['tier_id' => '1'],
            $condition
        );

        return $this;
    }

    /**
     * @param $e
     *
     * @return $this
     */
    public function addException($e)
    {
        $errorAggregator = $this->getErrorAggregator();
        $errorAggregator->addError(
            AbstractEntity::ERROR_CODE_SYSTEM_EXCEPTION,
            ProcessingError::ERROR_LEVEL_CRITICAL,
            null,
            null,
            $e->getMessage()
        );

        return $this;
    }
}
