<?php

namespace Ebizmarts\SagePaySuite\Model;

use Ebizmarts\SagePaySuite\Api\TokenManagementInterface;
use Ebizmarts\SagePaySuite\Model\Api\Post;
use Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger;
use Ebizmarts\SagePaySuite\Api\Data\ResultInterface;
use Ebizmarts\SagePaySuite\Api\Data\ResultInterfaceFactory;
use Ebizmarts\SagePaySuite\Model\Token as SagePaySuiteToken;
use Ebizmarts\SagePaySuite\Model\Token\VaultDetailsHandler;
use Magento\Framework\Exception\NoSuchEntityException;

class TokenManagement implements TokenManagementInterface
{
    /** @var Post */
    private $postApi;

    /** @var Config */
    private $config;

    /** @var */
    private $suiteLogger;

    /** @var ResultInterfaceFactory */
    private $resultInterfaceFactory;

    /** @var ResultInterface */
    private $resultInterface;

    /** @var SagePaySuiteToken */
    private $tokenModel;

    /** @var VaultDetailsHandler */
    private $vaultDetailsHandler;

    /**
     * DeleteTokenFromSagePay constructor.
     * @param Config $config
     * @param Post $postApi
     * @param Logger $suiteLogger
     * @param ResultInterfaceFactory $resultInterfaceFactory
     * @param SagePaySuiteToken $tokenModel
     * @param VaultDetailsHandler $vaultDetailsHandler
     */
    public function __construct(
        Config $config,
        Post $postApi,
        Logger $suiteLogger,
        ResultInterfaceFactory $resultInterfaceFactory,
        SagePaySuiteToken $tokenModel,
        VaultDetailsHandler $vaultDetailsHandler
    ) {
        $this->config      = $config;
        $this->postApi     = $postApi;
        $this->suiteLogger = $suiteLogger;
        $this->resultInterfaceFactory = $resultInterfaceFactory;
        $this->tokenModel = $tokenModel;
        $this->vaultDetailsHandler = $vaultDetailsHandler;
    }

    /**
     * @param string $token
     * @return string
     */
    public function deleteFromSagePay($token)
    {
        if (empty($this->config->getVendorname()) || empty($token)) {
            throw new NoSuchEntityException(
                __('Unable to delete token from Opayo: missing data to proceed')
            );
        }

        //generate delete POST request
        $data = [];
        $data["VPSProtocol"] = $this->config->getVPSProtocol();
        $data["TxType"] = "REMOVETOKEN";
        $data["Vendor"] = $this->config->getVendorname();
        $data["Token"] = $token;
        //send POST to Sage Pay
        $this->postApi->sendPost(
            $data,
            $this->_getRemoveServiceURL(),
            ["OK"]
        );

        return $token;
    }

    /**
     * @inheritdoc
     */
    public function deleteToken($tokenId, $customerId, $paymentMethod)
    {
        /** @var ResultInterface resultInterface */
        $this->resultInterface = $this->resultInterfaceFactory->create();
        if ($this->vaultDetailsHandler->deleteToken($tokenId, $customerId)) {
            $this->getSuccessResponseContent();
        } else {
            $this->getFailResponseContent('Unable to delete token');
        }
        return $this->resultInterface;
    }

    /**
     * @return string
     */
    private function _getRemoveServiceURL()
    {
        return $this->config->getMode() == Config::MODE_LIVE
            ? Config::URL_TOKEN_POST_REMOVE_LIVE
            : Config::URL_TOKEN_POST_REMOVE_TEST;
    }

    /**
     * @param $errorMessage
     */
    private function getFailResponseContent($errorMessage)
    {
        $this->resultInterface->setSuccess(false);
        $this->resultInterface->setErrorMessage(__("Something went wrong: %1", $errorMessage));
    }

    /**
     * @return void
     */
    private function getSuccessResponseContent()
    {
        $this->resultInterface->setSuccess(true);
    }
}
