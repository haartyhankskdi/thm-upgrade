<?php

namespace Ebizmarts\SagePaySuite\Model\Multishipping\Pi;

use Ebizmarts\SagePaySuite\Api\Data\PiRequestManagerFactory;
use Ebizmarts\SagePaySuite\Model\Api\ApiException;
use Ebizmarts\SagePaySuite\Model\Config;
use Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger;
use Ebizmarts\SagePaySuite\Model\ObjectLoader\OrderLoader;
use Ebizmarts\SagePaySuite\Model\PiRequestManagement\ThreeDSecureCallbackManagement;
use Ebizmarts\SagePaySuite\Model\RecoverCart;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;

class PiCallbackThreeDManagement
{
    private const DUPLICATED_CALLBACK_ERROR_MESSAGE = 'Duplicated 3D security callback received.';

    /** @var RequestInterface */
    private $httpRequest;

    /** @var OrderLoader */
    private $orderLoader;

    /** @var Logger */
    private $suiteLogger;

    /** @var PiRequestManagerFactory */
    private $piRequestManagerDataFactory;

    /** @var Config */
    private $config;

    /** @var ThreeDSecureCallbackManagement */
    private $requester;

    /** @var UrlInterface */
    private $urlBuilder;

    /** @var RecoverCart */
    private $recoverCart;

    /**
     * PiCallbackThreeDManagement constructor.
     * @param RequestInterface $httpRequest
     * @param OrderLoader $orderLoader
     * @param Logger $suiteLogger
     * @param PiRequestManagerFactory $piRequestManagerDataFactory
     * @param Config $config
     * @param ThreeDSecureCallbackManagement $requester
     * @param UrlInterface $urlBuilder
     * @param RecoverCart $recoverCart
     */
    public function __construct(
        RequestInterface $httpRequest,
        OrderLoader $orderLoader,
        Logger $suiteLogger,
        PiRequestManagerFactory $piRequestManagerDataFactory,
        Config $config,
        ThreeDSecureCallbackManagement $requester,
        UrlInterface $urlBuilder,
        RecoverCart $recoverCart
    ) {
        $this->httpRequest = $httpRequest;
        $this->orderLoader = $orderLoader;
        $this->suiteLogger = $suiteLogger;
        $this->piRequestManagerDataFactory = $piRequestManagerDataFactory;
        $this->config = $config;
        $this->requester = $requester;
        $this->urlBuilder = $urlBuilder;
        $this->recoverCart = $recoverCart;
    }

    /**
     * @param string $threeDValue
     * @return array
     */
    public function handleCallbackData($threeDValue)
    {
        try {
            $orderIds = $this->_getOrderIdsFromParams();
            $orderZero = $this->orderLoader->loadOrderById($orderIds[0]);

            if ($orderZero->getState() !== \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT) {
                return ['multishipping/checkout/success', Config::SUCCESS_STATUS];
            }

            /** @var \Ebizmarts\SagePaySuite\Api\Data\PiRequestManager $data */
            $data = $this->piRequestManagerDataFactory->create();

            $vpstxid = $this->_getTransactionId($orderIds[0]);
            $data->setTransactionId($vpstxid);
            $data->setCres($threeDValue);

            $data->setVendorName($this->config->getVendorname());
            $data->setMode($this->config->getMode());
            $data->setPaymentAction($this->config->getSagepayPaymentAction());
            $data->setOrderIds($orderIds);

            $this->requester->setRequestData($data);

            $response = $this->requester->placeOrder(true, $orderIds);

            if ($response->getErrorMessage() === null) {
                return ['multishipping/checkout/success', Config::SUCCESS_STATUS, $orderIds];
            } else {
                return ['checkout/cart', $response->getErrorMessage()];
            }
        } catch (ApiException $apiException) {
            $this->recoverCart->setShouldCancelOrders(true)->execute($this->_getOrderIdsFromParams());
            $this->suiteLogger->sageLog(
                Logger::LOG_EXCEPTION,
                $apiException->getTraceAsString(),
                [__METHOD__, __LINE__]
            );
        } catch (\RuntimeException $runtimeException) {
            $message = self::DUPLICATED_CALLBACK_ERROR_MESSAGE;
            $this->suiteLogger->sageLog(Logger::LOG_REQUEST, $message, [__METHOD__, __LINE__]);
            throw new \RuntimeException(__($this->getDuplicatedCallBackErrorMessage()));
        } catch (\Exception $e) {
            $this->recoverCart->setShouldCancelOrders(true)->execute($this->_getOrderIdsFromParams());
            $this->suiteLogger->sageLog(Logger::LOG_EXCEPTION, $e->getTraceAsString(), [__METHOD__, __LINE__]);
        }
    }

    /**
     * @return array
     */
    private function _getOrderIdsFromParams()
    {
        $orderQuantity = $this->httpRequest->getParam("quantity");
        $orderIds = [];

        for ($i = 0; $i < $orderQuantity; $i++) {
            $orderId = $this->httpRequest->getParam("orderId" . $i);
            $orderIds[] = $orderId;
        }

        return $orderIds;
    }

    private function _getTransactionId($orderId)
    {
        $order = $this->orderLoader->loadOrderById($orderId);
        $payment = $order->getPayment();
        $additionalInformation = $payment->getAdditionalInformation();

        return $additionalInformation['VPSTxId'];
    }

    private function getDuplicatedCallBackErrorMessage()
    {
        return self::DUPLICATED_CALLBACK_ERROR_MESSAGE;
    }
}
