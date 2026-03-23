<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Model;

use Ebizmarts\SagePaySuite\Api\TokenGetInterface;
use Ebizmarts\SagePaySuite\Model\Api\Post;
use Ebizmarts\SagePaySuite\Model\Token\Delete;
use Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger;
use Ebizmarts\SagePaySuite\Api\TokenManagementInterface;
use Ebizmarts\SagePaySuite\Api\TokenManagementInterfaceFactory;
use Ebizmarts\SagePaySuite\Api\Data\TokenInterfaceFactory;
use Ebizmarts\SagePaySuite\Api\Data\TokenInterface;
use Ebizmarts\SagePaySuite\Api\TokenModelInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * Sage Pay Token class
 */
class Token extends \Magento\Framework\Model\AbstractModel implements TokenModelInterface
{
    /**
     * @var Post
     */
    private $_postApi;

    /**
     * @var Config
     */
    private $_config;

    /** @var TokenManagementInterfaceFactory $deleteTokenFromSagePayFactory*/
    private $deleteTokenFromSagePayFactory;

    /**
     * @var Logger
     */
    private $_suiteLogger;

    /** @var Delete */
    private $tokenDelete;

    /** @var TokenGetInterface */
    private $tokenGet;

    /** @var TokenInterfaceFactory $tokenInterfaceFactory */
    private $tokenInterfaceFactory;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param Logger $suiteLogger
     * @param Api\Post $postApi
     * @param Config $config
     * @param TokenManagementInterfaceFactory $deleteTokenFromSagePayFactory
     * @param Delete $tokenDelete
     * @param TokenGetInterface $tokenGet
     * @param TokenInterfaceFactory $tokenInterfaceFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Logger $suiteLogger,
        Post $postApi,
        Config $config,
        TokenManagementInterfaceFactory $deleteTokenFromSagePayFactory,
        Delete $tokenDelete,
        TokenGetInterface $tokenGet,
        TokenInterfaceFactory $tokenInterfaceFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_suiteLogger           = $suiteLogger;
        $this->_logger                = $context->getLogger();
        $this->_postApi               = $postApi;
        $this->_config                = $config;
        $this->deleteTokenFromSagePayFactory = $deleteTokenFromSagePayFactory;
        $this->tokenDelete            = $tokenDelete;
        $this->tokenGet               = $tokenGet;
        $this->tokenInterfaceFactory  = $tokenInterfaceFactory;
    }

    /**
     * Init model
     *
     * @return void
     */
    // @codingStandardsIgnoreStart
    protected function _construct()
    {
        $this->_init('Ebizmarts\SagePaySuite\Model\ResourceModel\Token');
    }
    // @codingStandardsIgnoreEnd

    /**
     * Saves a token to the db
     *
     * @param TokenInterface $tokenInterface
     * @return $this
     */
    public function saveToken($tokenInterface)
    {
        if (empty($tokenInterface->getCustomerId())) {
            return $this;
        }

        $paymentCode = $tokenInterface->getPaymentMethod() === null
            ? Config::METHOD_SERVER
            : $tokenInterface->getPaymentMethod();
        $tokenVaultId = $tokenInterface->getVaultId() === null ? 0 : $tokenInterface->getVaultId();

        $this->setCustomerId($tokenInterface->getCustomerId());
        $this->setToken($tokenInterface->getToken());
        $this->setCcType($tokenInterface->getCcType());
        $this->setCcLast4($tokenInterface->getCcLastFour());
        $this->setCcExpMonth($tokenInterface->getCcExpMonth());
        $this->setCcExpYear($tokenInterface->getCcExpYear());
        $this->setVendorname($tokenInterface->getVendroName());
        $this->setPaymentMethod($paymentCode);
        $this->setVaultId($tokenVaultId);
        $this->save();

        return $this;
    }

    /**
     * Gets an array of the tokens owned by a customer and for a certain vendorname
     *
     * @param $customerId
     * @param $vendorname
     * @return array
     */
    public function getCustomerTokens($customerId, $vendorname)
    {
        if (!empty($customerId)) {
            $this->setData([]);
            $this->getResource()->getCustomerTokens($this, $customerId, $vendorname);
            return $this->_data;
        }
        return [];
    }

    /**
     * @param $customerId
     * @param $vendorname
     * @return array
     */
    public function getCustomerTokensToShowOnAccount($customerId, $vendorname)
    {
        $tokens = [];
        $serverTokens = $this->getCustomerTokens($customerId, $vendorname);
        foreach ($serverTokens as $token) {
            $token['isVault'] = false;
            $tokens[] = $token;
        }
        return $tokens;
    }

    /**
     * Delete token from db and Sage Pay
     * @throws NoSuchEntityException
     * @throws \Exception
     */
    public function deleteToken()
    {
        //delete from sagepay
        /** @var TokenManagementInterface $deleteTokenFromSagepay */
        $deleteTokenFromSagepay = $this->deleteTokenFromSagePayFactory->create();
        $deleteTokenFromSagepay->deleteFromSagePay($this->getToken());

        // delete from magento vault if it exists
        if ($this->getVaultId() !== 0) {
            $tokenVault = $this->tokenGet->getTokenById($this->getVaultId());
            if ($tokenVault) {
                $this->tokenDelete->removeTokenFromVault($tokenVault);
            }
        }

        if ($this->getId()) {
            $this->delete();
        }
    }

    /**
     * load from db
     *
     * @param $tokenId
     * @return \Ebizmarts\SagePaySuite\Model\Token
     */
    public function loadToken($tokenId)
    {
        $token = $this->getResource()->getTokenById($tokenId);

        if ($token === null) {
            return null;
        }

        $this->setId($token["id"])
            ->setCustomerId($token["customer_id"])
            ->setToken($token["token"])
            ->setCcType($token["cc_type"])
            ->setCcLast4($token["cc_last_4"])
            ->setCcExpMonth($token["cc_exp_month"])
            ->setCcExpYear($token["cc_exp_year"])
            ->setVendorname($token["vendorname"])
            ->setCreatedAt($token["created_at"])
            ->setStoreId($token["store_id"])
            ->setPaymentMethod($token['payment_method'])
            ->setVaultId((int)$token['vault_id']);

        return $this;
    }

    /**
     * Checks whether the token is owned by the customer
     *
     * @param $customerId
     * @return bool
     */
    public function isOwnedByCustomer($customerId)
    {
        try {
            if (empty($customerId) || empty($this->getId())) {
                throw new NoSuchEntityException(
                    __('Unable to delete token from Opayo: missing data to proceed')
                );
            }
            return $this->getResource()->isTokenOwnedByCustomer($customerId, $this->getId());
        } catch (NoSuchEntityException $e) {
            $this->_suiteLogger->logException($e);
            return false;
        }
    }

    /**
     * Checks whether the customer is using all the available token slots.
     * @param $customerId
     * @param $vendorname
     * @return bool
     */
    public function isCustomerUsingMaxTokenSlots($customerId, $vendorname)
    {
        if (empty($customerId)) {
            return true;
        }
        $this->setData([]);
        $this->getResource()->getCustomerTokens($this, $customerId, $vendorname);
        return count($this->_data) >= $this->_config->getMaxTokenPerCustomer();
    }

    /**
     * @param $token
     * @return $this|null
     */
    public function loadTokenByToken($token)
    {
        $token = $this->getResource()->getTokenByToken($token);

        if ($token === null || $token === false) {
            return null;
        }

        $this->setId($token["id"])
            ->setCustomerId($token["customer_id"])
            ->setToken($token["token"])
            ->setCcType($token["cc_type"])
            ->setCcLast4($token["cc_last_4"])
            ->setCcExpMonth($token["cc_exp_month"])
            ->setCcExpYear($token["cc_exp_year"])
            ->setVendorname($token["vendorname"])
            ->setCreatedAt($token["created_at"])
            ->setStoreId($token["store_id"])
            ->setPaymentMethod($token['payment_method'])
            ->setVaultId((int)$token['vault_id']);

        return $this;
    }

    /**
     * Gets an array of expired tokens $currentDate param must be on the following format: 'Y-m-01'
     *
     * @param $currentDate
     * @return array
     */
    public function getExpiredTokens($currentDate)
    {
        $data = [];
        $results = $this->getResource()->getExpiredTokens($this, $currentDate);
        foreach ($results as $result) {
            array_push($data, $this->toDto($result));
        }

        return $data;
    }

    private function toDto($token)
    {
        /** @var TokenInterface $tokenInterface */
        $tokenInterface = $this->tokenInterfaceFactory->create();
        $tokenInterface->setId($token['id']);
        $tokenInterface->setCustomerId($token[TokenInterface::CUSTOMER_ID]);
        $tokenInterface->setToken($token[TokenInterface::TOKEN]);
        $tokenInterface->setCcType($token[TokenInterface::CC_TYPE]);
        $tokenInterface->setCcLastFour($token[TokenInterface::CC_LAST_FOUR]);
        $tokenInterface->setCcExpMonth($token[TokenInterface::CC_EXP_MONTH]);
        $tokenInterface->setCcExpYear($token[TokenInterface::CC_EXP_YEAR]);
        $tokenInterface->setVendroName($token[TokenInterface::VENDOR_NAME]);
        $tokenInterface->setCreatedAt($token[TokenInterface::CREATED_AT]);
        $tokenInterface->setStoreId($token[TokenInterface::STORE_ID]);
        $tokenInterface->setPaymentMethod($token[TokenInterface::PAYMENT_METHOD]);
        $tokenInterface->setVaultId($token[TokenInterface::VAULT_ID]);

        return $tokenInterface;
    }
}
