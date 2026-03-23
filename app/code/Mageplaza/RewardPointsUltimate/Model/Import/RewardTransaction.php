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
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\StringUtils;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\AbstractEntity;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\ImportFactory;
use Magento\ImportExport\Model\ResourceModel\Helper;
use Magento\Sales\Model\OrderFactory;
use Mageplaza\RewardPoints\Model\AccountFactory;
use Mageplaza\RewardPoints\Model\ActionFactory;
use Mageplaza\RewardPoints\Model\Source\ActionType;
use Mageplaza\RewardPoints\Model\Source\Status;
use Mageplaza\RewardPoints\Model\TransactionFactory;
use Mageplaza\RewardPointsUltimate\Helper\Data;

/**
 * Class RewardTransaction
 * @package Mageplaza\RewardPointsUltimate\Model\Import
 */
class RewardTransaction extends AbstractEntity
{
    /**
     * columns
     */
    const REWARD_ID         = 'reward_id';
    const TRANSACTION_ID    = 'transaction_id';
    const CUSTOMER_ID       = 'customer_id';
    const ACTION_CODE       = 'action_code';
    const STORE_ID          = 'store_id';
    const POINT_AMOUNT      = 'point_amount';
    const POINT_REMAINING   = 'point_remaining';
    const POINT_USED        = 'point_used';
    const STATUS            = 'status';
    const ORDER_ID          = 'order_id';
    const ACTION_TYPE       = 'action_type';
    const EXPIRATION_DATE   = 'expiration_date';
    const CREATED_AT        = 'created_at';
    const EXTRA_CONTENT     = 'extra_content';
    const EXPIRE_EMAIL_SEND = 'expire_email_sent';
    const COMMENT           = 'comment';

    /**#@+
     * Error codes
     */
    const ERROR_REWARD_DISABLE = 'rewardDisable';

    /**
     * Valid column names
     *
     * @array
     */
    protected $validColumnNames
        = [
            self::REWARD_ID,
            self::TRANSACTION_ID,
            self::POINT_AMOUNT,
            self::ORDER_ID,
            self::STORE_ID,
            self::ACTION_CODE,
            self::ACTION_TYPE,
            self::POINT_REMAINING,
            self::POINT_USED,
            self::ORDER_ID,
            self::EXTRA_CONTENT,
            self::EXPIRE_EMAIL_SEND,
            self::CREATED_AT,
            self::EXPIRATION_DATE,
            self::COMMENT
        ];

    /** @inheritdoc */
    protected $masterAttributeCode = 'customer_id';

    /**
     * Permanent entity columns.
     *
     * @var string[]
     */
    protected $_permanentAttributes = [
        self::CUSTOMER_ID,
        self::ACTION_CODE,
        self::ACTION_TYPE,
        self::POINT_AMOUNT,
        self::STATUS
    ];

    /** @inheritdoc */
    protected $_availableBehaviors = [Import::BEHAVIOR_ADD_UPDATE, Import::BEHAVIOR_REPLACE];

    /**
     * @var TransactionFactory
     */
    protected $transactionFactory;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var Status
     */
    protected $status;

    /**
     * @var AccountFactory
     */
    protected $accountFactory;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var ActionFactory
     */
    protected $actionFactory;

    /**
     * @var array
     */
    protected $rewardCustomerRegistry = [];

    /**
     * @var array
     */
    protected $customerRegistry = [];

    /**
     * @var array
     */
    protected $currentResult = [];

    /**
     * @var ActionType
     */
    protected $actionType;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * RewardTransaction constructor.
     *
     * @param StringUtils $string
     * @param ScopeConfigInterface $scopeConfig
     * @param ImportFactory $importFactory
     * @param Helper $resourceHelper
     * @param ResourceConnection $resource
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @param AccountFactory $accountFactory
     * @param ActionFactory $actionFactory
     * @param TransactionFactory $transactionFactory
     * @param DateTime $dateTime
     * @param CustomerFactory $customerFactory
     * @param OrderFactory $orderFactory
     * @param ActionType $actionType
     * @param Status $status
     * @param Data $helperData
     * @param array $data
     */
    public function __construct(
        StringUtils $string,
        ScopeConfigInterface $scopeConfig,
        ImportFactory $importFactory,
        Helper $resourceHelper,
        ResourceConnection $resource,
        ProcessingErrorAggregatorInterface $errorAggregator,
        AccountFactory $accountFactory,
        ActionFactory $actionFactory,
        TransactionFactory $transactionFactory,
        DateTime $dateTime,
        CustomerFactory $customerFactory,
        OrderFactory $orderFactory,
        ActionType $actionType,
        Status $status,
        Data $helperData,
        array $data = []
    ) {
        $this->accountFactory = $accountFactory;
        $this->transactionFactory = $transactionFactory;
        $this->dateTime = $dateTime;
        $this->customerFactory = $customerFactory;
        $this->orderFactory = $orderFactory;
        $this->actionFactory = $actionFactory;
        $this->actionType = $actionType;
        $this->status = $status;
        $this->helperData   = $helperData;

        parent::__construct($string, $scopeConfig, $importFactory, $resourceHelper, $resource, $errorAggregator, $data);

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
        if ($this->getBehavior() === Import::BEHAVIOR_REPLACE) {
            $this->_permanentAttributes[] = self::TRANSACTION_ID;
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
        return 'mp_reward_transaction';
    }

    /**
     * @param array $rowData
     * @param int $rowNum
     *
     * @return bool
     */
    public function validateAction($rowData, $rowNum)
    {
        try {
            $actions = $this->actionFactory->getOptionHash();
            $action = $rowData[self::ACTION_CODE];
            if (!isset($actions[$action])) {
                $this->addRowError(__('Action code doesn\'t exist'), $rowNum);

                return false;
            }
        } catch (Exception $e) {
            $this->addRowError($e->getMessage(), $rowNum);

            return false;
        }

        return true;
    }

    /**
     * @param array $rowData
     * @param int $rowNum
     *
     * @return bool
     */
    public function validateActionType($rowData, $rowNum)
    {
        $action = $rowData[self::ACTION_TYPE];
        if (!isset($this->actionType->getOptionArray()[$action])) {
            $this->addRowError(__('Action code doesn\'t exist'), $rowNum);

            return false;
        }

        return true;
    }

    /**
     * @param string|int $id
     * @param int $rowNum
     *
     * @return Customer
     */
    public function validateCustomerById($id, $rowNum)
    {
        $customer = $this->getCustomerById($id);
        if (!$customer->getId()) {
            $this->addRowError(__('Customer doesn\'t exist'), $rowNum);
        }

        return $customer;
    }

    /**
     * @param int|string $id
     *
     * @return mixed
     */
    public function getCustomerById($id)
    {
        if (!isset($this->customerRegistry[$id])) {
            $this->customerRegistry[$id] = $this->customerFactory->create()->load($id);
        }

        return $this->customerRegistry[$id];
    }

    /**
     * @param array $rowData
     * @param int $rowNum
     * @param Customer $customer
     */
    public function validateReplace($rowData, $rowNum, $customer)
    {
        $transaction = $this->transactionFactory->create()->load($rowData[self::TRANSACTION_ID]);
        if (!$transaction->getId()) {
            $this->addRowError(__('Transaction doesn\'t exist.'), $rowNum);
        }

        $rewardCustomer = $this->accountFactory->create()->load($customer->getId(), 'customer_id');
        if (!$rewardCustomer->getId()) {
            $this->addRowError(__('We can\'t find a reward account who matches the customer.'), $rowNum);
        }

        $this->currentResult[self::TRANSACTION_ID] = $rowData [self::TRANSACTION_ID];
        $this->currentResult[self::REWARD_ID] = $rewardCustomer->getId();
    }

    /**
     * @param array $rowData
     * @param int $rowNum
     * @param Customer $customer
     */
    public function validateStore($rowData, $rowNum, $customer)
    {
        if (isset($rowData[self::STORE_ID])) {
            if ((int)$customer->getStoreId() !== (int)$rowData[self::STORE_ID]) {
                $this->addRowError(__('We can\'t find a customer who matches this store id.'), $rowNum);
            }
            $this->currentResult[self::STORE_ID] = $rowData[self::STORE_ID];
        } else {
            $this->currentResult[self::STORE_ID] = $customer->getStoreId();
        }
    }

    /**
     * @param array $rowData
     * @param int $rowNum
     */
    public function validatePointRemaining($rowData, $rowNum)
    {
        $this->currentResult[self::POINT_REMAINING] = 0;
        if (isset($rowData[self::POINT_REMAINING])) {
            if ($rowData[self::POINT_REMAINING] < 0) {
                $this->addRowError(__('Point remaining must be equals or greater than zero'), $rowNum);
            } else {
                $this->currentResult[self::POINT_REMAINING] = $rowData[self::POINT_REMAINING];
            }
        }
    }

    /**
     * @param array $rowData
     * @param int $rowNum
     */
    public function validatePointUsed($rowData, $rowNum)
    {
        if (isset($rowData[self::POINT_USED])) {
            if ($rowData[self::POINT_USED] < 0) {
                $this->addRowError(__('Point used must be equals or greater than zero'), $rowNum);
            } else {
                $this->currentResult[self::POINT_USED] = $rowData[self::POINT_USED];
            }
        } else {
            $this->currentResult[self::POINT_USED] = 0;
        }
    }

    /**
     * @param array $rowData
     * @param int $rowNum
     */
    public function validateExpireEmailSend($rowData, $rowNum)
    {
        $this->currentResult[self::EXPIRE_EMAIL_SEND] = 0;
        if (isset($rowData[self::EXPIRE_EMAIL_SEND])) {
            if (in_array($rowData[self::EXPIRE_EMAIL_SEND], [0, 1, '0', '1'], true)) {
                $this->currentResult[self::EXPIRE_EMAIL_SEND] = $rowData[self::EXPIRE_EMAIL_SEND];
            } else {
                $this->addRowError(__('%1 invalid', self::EXPIRE_EMAIL_SEND), $rowNum);
            }
        }
    }

    /**
     * @param array $rowData
     * @param int $rowNum
     * @param string $field
     * @param bool $isDefault
     */
    public function validateDate($rowData, $rowNum, $field, $isDefault = false)
    {
        $date = null;
        if (isset($rowData[$field]) && $rowData[$field]) {
            $date = $this->dateTime->gmtDate(null, $rowData[$field]);
            if (!$date) {
                $this->addRowError(__('%1 at invalid', $field), $rowNum);
            }
        } elseif ($isDefault) {
            $date = $this->dateTime->gmtDate();
        }

        $this->currentResult[$field] = $date;
    }

    /**
     * Row validation.
     *
     * @param array $rowData
     * @param int $rowNum
     *
     * @return bool
     */
    public function validateRow(array $rowData, $rowNum)
    {
        $this->currentResult = [];
        $this->_validatedRows[$rowNum] = true;
        foreach ($this->_permanentAttributes as $value) {
            if (empty($rowData[$value])) {
                $this->addRowError(__('%1 is empty', $value), $rowNum);
            }
        }

        $customer = $this->validateCustomerById($rowData[self::CUSTOMER_ID], $rowNum);
        if (Import::BEHAVIOR_REPLACE === $this->getBehavior()) {
            $this->validateReplace($rowData, $rowNum, $customer);
        }

        $this->validateAction($rowData, $rowNum);
        $this->validateActionType($rowData, $rowNum);
        if (!isset($this->status->getOptionArray()[$rowData[self::STATUS]])) {
            $this->addRowError(__('Invalid status.'), $rowNum);
        }

        $this->validateStore($rowData, $rowNum, $customer);

        if ($rowData[self::POINT_AMOUNT] < 0) {
            $this->addRowError(__('Point Amount must be equals or greater than zero'), $rowNum);
        }

        $this->currentResult[self::CUSTOMER_ID] = $rowData[self::CUSTOMER_ID];
        $this->currentResult[self::ACTION_CODE] = $rowData[self::ACTION_CODE];
        $this->currentResult[self::ACTION_TYPE] = $rowData[self::ACTION_TYPE];
        $this->currentResult[self::POINT_AMOUNT] = $rowData[self::POINT_AMOUNT];
        $this->currentResult[self::STATUS] = $rowData[self::STATUS];
        $this->validatePointRemaining($rowData, $rowNum);
        $this->validatePointUsed($rowData, $rowNum);

        $extraContent = [];
        if (isset($rowData[self::EXTRA_CONTENT])) {
            $extraContent = Data::jsonDecode($rowData[self::EXTRA_CONTENT]);
        }

        $this->currentResult[self::ORDER_ID] = 0;
        if (isset($rowData[self::ORDER_ID])) {
            $order = $this->orderFactory->create()->load($rowData[self::ORDER_ID]);
            if ($order->getId()) {
                $extraContent['increment_id'] = $order->getIncrementId();
            }

            $this->currentResult[self::ORDER_ID] = $rowData[self::ORDER_ID];
        }

        $this->validateExpireEmailSend($rowData, $rowNum);
        $this->validateDate($rowData, $rowNum, self::CREATED_AT, true);
        $this->validateDate($rowData, $rowNum, self::EXPIRATION_DATE);
        if (isset($rowData[self::COMMENT])) {
            $extraContent[self::COMMENT] = $rowData[self::COMMENT];
        }

        $this->currentResult[self::EXTRA_CONTENT] = Data::jsonEncode($extraContent);

        return !$this->getErrorAggregator()->isRowInvalid($rowNum);
    }

    /**
     * @param int|string $id
     *
     * @return mixed
     */
    public function getRewardCustomer($id)
    {
        if (!isset($this->rewardCustomerRegistry[$id])) {
            $rewardCustomer = $this->accountFactory->create()->load($id, self::CUSTOMER_ID);
            $this->rewardCustomerRegistry[$id] = $rewardCustomer;
        }

        return $this->rewardCustomerRegistry[$id];
    }

    /**
     * @return $this|bool
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

                if (!isset($this->currentResult[self::REWARD_ID])) {
                    $customerId = $this->currentResult[self::CUSTOMER_ID];
                    $rewardCustomer = $this->getRewardCustomer($customerId);
                    if (!$rewardCustomer->getId()) {
                        $rewardCustomer->create(
                            $this->currentResult[self::STORE_ID],
                            [
                                'point_balance' => 0,
                                'is_active' => 1,
                                self::CUSTOMER_ID => $customerId
                            ]
                        );

                        $this->rewardCustomerRegistry[$customerId] = $rewardCustomer;
                    }

                    $this->currentResult[self::REWARD_ID] = $rewardCustomer->getId();
                }

                $entityList[] = $this->currentResult;
            }
        }

        $connection->beginTransaction();
        $updated = [];
        try {
            $mainTable = $this->transactionFactory->create()->getResource()->getMainTable();
            if (Import::BEHAVIOR_REPLACE === $this->getBehavior()) {
                $updated = $entityList;
                $entityList = [];
                array_pop($this->validColumnNames);
                $connection->insertOnDuplicate($mainTable, $updated, $this->validColumnNames);
                $this->updateTierCustomers($updated);
            } else {
                $connection->insertMultiple(
                    $mainTable,
                    $entityList
                );
                $this->updateTierCustomers($entityList);
            }
            $connection->commit();
        } catch (Exception $e) {
            $entityList = [];
            $updated = $entityList;
            $errorAggregator = $this->getErrorAggregator();
            $errorAggregator->addError(
                AbstractEntity::ERROR_CODE_SYSTEM_EXCEPTION,
                ProcessingError::ERROR_LEVEL_CRITICAL,
                null,
                null,
                $e->getMessage()
            );
            $connection->rollBack();
        }

        $this->updateItemsCounterStats($entityList, $updated);

        return $this;
    }

    /**
     * @param array $entityList
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function updateTierCustomers($entityList)
    {
        foreach ($entityList as $item) {
            if (isset($item[self::CUSTOMER_ID])) {
                $this->helperData->updateTier($item[self::CUSTOMER_ID]);
            }
        }
    }
}
