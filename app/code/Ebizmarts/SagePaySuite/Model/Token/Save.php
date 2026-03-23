<?php

namespace Ebizmarts\SagePaySuite\Model\Token;

use Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Model\Order\Payment;
use Magento\Vault\Api\Data\PaymentTokenFactoryInterface;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;

class Save
{
    /**
     * @var Logger
     */
    private $suiteLogger;

    /** @var PaymentTokenManagementInterface */
    private $paymentTokenManagement;

    /** @var PaymentTokenFactoryInterface */
    private $paymentTokenFactory;

    /** @var PaymentTokenRepositoryInterface */
    private $paymentTokenRepository;

    /** @var Json */
    private $jsonSerializer;

    /**
     * Save constructor.
     * @param Logger $suiteLogger
     * @param PaymentTokenManagementInterface $paymentTokenManagement
     * @param PaymentTokenFactoryInterface $paymentTokenFactory
     * @param Json $jsonSerializer
     * @param PaymentTokenRepositoryInterface $paymentTokenRepository
     */
    public function __construct(
        Logger $suiteLogger,
        PaymentTokenManagementInterface $paymentTokenManagement,
        PaymentTokenFactoryInterface $paymentTokenFactory,
        Json $jsonSerializer,
        PaymentTokenRepositoryInterface $paymentTokenRepository
    ) {
        $this->suiteLogger            = $suiteLogger;
        $this->paymentTokenManagement = $paymentTokenManagement;
        $this->paymentTokenFactory    = $paymentTokenFactory;
        $this->jsonSerializer         = $jsonSerializer;
        $this->paymentTokenRepository = $paymentTokenRepository;
    }

    /**
     * @param Payment $payment
     * @param int $customerId
     * @param string $token
     *
     */
    public function saveToken($payment, $customerId, $token)
    {
        try {
            if (!empty($customerId)) {
                $paymentToken = $this->createVaultPaymentToken($payment, $customerId, $token);
                if ($paymentToken !== null) {
                    $this->paymentTokenManagement->saveTokenWithPaymentLink($paymentToken, $payment);
                } else {
                    throw new CouldNotSaveException(__('Unable to save token: payment token is null'));
                }
            } else {
                throw new NoSuchEntityException(__('Unable to create token: customer id is empty'));
            }
        } catch (NoSuchEntityException $e) {
            $this->suiteLogger->logException($e);
        } catch (CouldNotSaveException $e) {
            $this->suiteLogger->logException($e);
        }
    }
    public function saveMovedToken($token, $integration)
    {
        $paymentToken = $this->paymentTokenFactory->create(PaymentTokenFactoryInterface::TOKEN_TYPE_CREDIT_CARD);
        $paymentToken->setGatewayToken(trim($token->getToken(), "{}"));
        $paymentToken->setTokenDetails($this->createTokenDetailsFromMovedToken($token));
        $paymentToken->setCustomerId($token->getCustomerId());
        $paymentToken->setPaymentMethodCode($integration);
        $paymentToken->setPublicHash($this->generatePublicHash($token->getToken()));
        $paymentToken->setIsVisible(true);
        $paymentToken->setIsActive(true);
        $paymentToken->setData('sagepaysuite_vendorname', $token->getVendorname());

        $this->paymentTokenRepository->save($paymentToken);
    }

    /**
     * @param Payment $payment
     * @param int $customerId
     * @param string $token
     * @return \Magento\Vault\Api\Data\PaymentTokenInterface|null
     * @throws NoSuchEntityException
     */
    public function createVaultPaymentToken($payment, $customerId, $token)
    {
        if (empty($token)) {
            throw new NoSuchEntityException(__('Unable to create token: token is empty'));
        }

        $paymentToken = $this->paymentTokenFactory->create(PaymentTokenFactoryInterface::TOKEN_TYPE_CREDIT_CARD);
        $paymentToken->setGatewayToken($token);
        $paymentToken->setTokenDetails($this->createTokenDetails($payment));
        $paymentToken->setCustomerId($customerId);
        $paymentToken->setPaymentMethodCode($payment->getMethod());
        $paymentToken->setPublicHash($this->generatePublicHash($token));
        $paymentToken->setIsVisible(true);
        $paymentToken->setIsActive(true);
        $paymentToken->setData('sagepaysuite_vendorname', $payment->getAdditionalInformation('vendorname'));

        return $paymentToken;
    }

    /**
     * @param Payment $payment
     * @return string
     */
    private function createTokenDetails($payment)
    {
        switch ($payment->getCcType()) {
            case 'VISA':
            case 'UKE':
            case 'DELTA':
                $ccType = 'VI';
                break;
            case 'MC':
            case 'MCDEBIT':
                $ccType = 'MC';
                break;
            case 'MAESTRO':
                $ccType = 'MAESTRO';
                break;
            case 'AMEX':
                $ccType = 'AMEX';
                break;
            case 'DC':
                $ccType = 'DC';
                break;
            case 'JCB':
                $ccType = 'JCB';
                break;
            default:
                $ccType = $payment->getCcType();
                break;
        }
        $tokenDetails = [
            'type' => $ccType,
            'maskedCC' => $payment->getCcLast4(),
            'expirationDate' => $payment->getCcExpMonth() . '/' . $payment->getCcExpYear()
        ];

        return $this->convertArrayToJSON($tokenDetails);
    }

    /**
     * @param \Ebizmarts\SagePaySuite\Model\Token $token
     * @return string
     */
    private function createTokenDetailsFromMovedToken($token)
    {
        // todo
        switch ($token->getCcType()) {
            case 'VISA':
            case 'UKE':
            case 'DELTA':
                $ccType = 'VI';
                break;
            case 'MC':
            case 'MCDEBIT':
                $ccType = 'MC';
                break;
            case 'MAESTRO':
                $ccType = 'MAESTRO';
                break;
            case 'AMEX':
                $ccType = 'AMEX';
                break;
            case 'DC':
                $ccType = 'DC';
                break;
            case 'JCB':
                $ccType = 'JCB';
                break;
            default:
                $ccType = $token->getCcType();
                break;
        }
        $tokenDetails = [
            'type' => $ccType,
            'maskedCC' => $token->getCcLast4(),
            'expirationDate' => $token->getCcExpMonth() . '/' . $token->getCcExpYear()
        ];

        return $this->convertArrayToJSON($tokenDetails);
    }

    /**
     * @param array $array
     * @return string
     */
    private function convertArrayToJson($array)
    {
        return str_replace("\\", "", $this->jsonSerializer->serialize($array));
    }

    /**
     * @param string $token
     * @return string
     */
    private function generatePublicHash($token)
    {
        return hash('md5', $token);
    }
}
