<?php

namespace Ebizmarts\SagePaySuite\Model\Token;

use Ebizmarts\SagePaySuite\Api\TokenGetInterface;
use Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger;
use Ebizmarts\SagePaySuite\Api\TokenManagementInterface;
use Ebizmarts\SagePaySuite\Api\TokenManagementInterfaceFactory;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Model\Order\Payment;

class VaultDetailsHandler
{
    /** @var Logger */
    private $suiteLogger;

    /** @var Save */
    private $tokenSave;

    /** @var TokenGetInterface */
    private $tokenGet;

    /** @var Delete */
    private $tokenDelete;

    /** @var TokenManagementInterfaceFactory */
    private $deleteTokenFromSagePayFactory;

    /** @var ManagerInterface */
    private $messageManager;

    /**
     * VaultDetailsHandler constructor.
     * @param Logger $suiteLogger
     * @param Save $tokenSave
     * @param TokenGetInterface $tokenGet
     * @param Delete $tokenDelete
     * @param TokenManagementInterfaceFactory $deleteTokenFromSagePayFactory
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        Logger $suiteLogger,
        Save $tokenSave,
        TokenGetInterface $tokenGet,
        Delete $tokenDelete,
        TokenManagementInterfaceFactory $deleteTokenFromSagePayFactory,
        ManagerInterface $messageManager
    ) {
        $this->suiteLogger            = $suiteLogger;
        $this->tokenSave              = $tokenSave;
        $this->tokenGet               = $tokenGet;
        $this->tokenDelete            = $tokenDelete;
        $this->deleteTokenFromSagePayFactory = $deleteTokenFromSagePayFactory;
        $this->messageManager         = $messageManager;
    }

    /**
     * @param Payment $payment
     * @param int $customerId
     * @param string $token
     *
     */
    public function saveToken($payment, $customerId, $token)
    {
        $this->tokenSave->saveToken($payment, $customerId, $token);
    }

    /**
     * @param int $customerId
     * @param string $method
     * @param string $vendor
     * @return array
     */
    public function getTokensFromCustomerToShowOnGrid($customerId, $method, $vendor)
    {
        return $this->tokenGet->getTokensFromCustomerToShowOnGrid($customerId, $method, $vendor);
    }

    /**
     * @param int $tokenId
     * @param int $customerId
     * @return bool
     */
    public function deleteToken($tokenId, $customerId)
    {
        try {
            $token = $this->tokenGet->getTokenById($tokenId);
            if ($token->getCustomerId() !== $customerId) {
                throw new AuthenticationException(
                    __('Unable to delete token from Opayo: customer does not own the token')
                );
            }
            /** @var TokenManagementInterface $deleteTokenFromSagepay */
            $deleteTokenFromSagepay = $this->deleteTokenFromSagePayFactory->create();
            $deleteTokenFromSagepay->deleteFromSagePay($token->getGatewayToken());
            return $this->tokenDelete->removeTokenFromVault($token);
        } catch (AuthenticationException | NoSuchEntityException $e) {
            $this->suiteLogger->logException($e);
            return false;
        }
    }

    /**
     * Once we move server to use Vault, this function will not be needed.
     *
     * @param int $customerId
     * @param string $method
     * @param string $vendor
     * @return array
     */
    public function getTokensFromCustomerToShowOnAccount($customerId, $method, $vendor)
    {
        $vaultTokens = $this->tokenGet->getTokensFromCustomerToShowOnGrid($customerId, $method, $vendor);
        $tokens = [];
        foreach ($vaultTokens as $token) {
            $token['isVault'] = true;
            $tokens[] = $token;
        }

        return $tokens;
    }
}
