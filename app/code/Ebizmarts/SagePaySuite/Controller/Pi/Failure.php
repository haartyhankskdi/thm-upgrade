<?php

namespace Ebizmarts\SagePaySuite\Controller\Pi;

use Ebizmarts\SagePaySuite\Api\SagePayData\PiInstructionResponse;
use Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultInterface;
use Ebizmarts\SagePaySuite\Model\Api\ApiException;
use Ebizmarts\SagePaySuite\Model\Api\PIRest;
use Ebizmarts\SagePaySuite\Model\Config;
use Ebizmarts\SagePaySuite\Model\CryptAndCodeData;
use Ebizmarts\SagePaySuite\Model\ObjectLoader\OrderLoader;
use Ebizmarts\SagePaySuite\Model\RecoverCart;
use Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger;
use Magento\Checkout\Model\Type\Onepage;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Model\Order;

class Failure implements ActionInterface, CsrfAwareActionInterface
{
    /** @var Config */
    private $config;

    /** @var Onepage */
    private $onepage;

    /** @var CartRepositoryInterface */
    private $quoteRepository;

    /** @var RecoverCart */
    private $recoverCart;

    /** @var OrderLoader */
    private $orderLoader;

    /** @var PIRest */
    private $piRestApi;

    /** @var Logger */
    private $suiteLogger;

    /** @var CryptAndCodeData */
    private $cryptAndCode;

    /** @var RequestInterface */
    private $request;

    /** @var RedirectInterface */
    private $redirect;

    /** @var ResponseInterface */
    private $response;

    /** @var ManagerInterface */
    private $messageManager;

    private $okStatuses = [
        Config::SUCCESS_STATUS,
        Config::AUTH3D_V2_REQUIRED_STATUS
    ];

    /**
     * @param Onepage $onepage
     * @param Config $config
     * @param CartRepositoryInterface $quoteRepository
     * @param RecoverCart $recoverCart
     * @param OrderLoader $orderLoader
     * @param PIRest $piRestApi
     * @param Logger $suiteLogger
     * @param CryptAndCodeData $cryptAndCode
     * @param RequestInterface $request
     * @param RedirectInterface $redirect
     * @param ResponseInterface $response
     * @param ManagerInterface $manager
     */
    public function __construct(
        Onepage $onepage,
        Config $config,
        CartRepositoryInterface $quoteRepository,
        RecoverCart $recoverCart,
        OrderLoader $orderLoader,
        PIRest $piRestApi,
        Logger $suiteLogger,
        CryptAndCodeData $cryptAndCode,
        RequestInterface $request,
        RedirectInterface $redirect,
        ResponseInterface $response,
        ManagerInterface $manager
    ) {
        $this->config = $config;
        $this->onepage = $onepage;
        $this->quoteRepository = $quoteRepository;
        $this->recoverCart = $recoverCart;
        $this->orderLoader = $orderLoader;
        $this->piRestApi = $piRestApi;
        $this->piRestApi->setPaymentMethod();
        $this->suiteLogger = $suiteLogger;
        $this->cryptAndCode = $cryptAndCode;
        $this->config->setMethodCode(Config::METHOD_PI);
        $this->request  = $request;
        $this->redirect = $redirect;
        $this->response = $response;
        $this->messageManager = $manager;
    }

    /**
     * @return ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $session = $this->onepage->getCheckout();
        $params = $this->getRequest()->getParams();
        if (isset($params['orderId']) && $params['orderId'] !== null) {
            $encryptedOrderId = $params['orderId'];
            $orderId = $this->cryptAndCode->decodeAndDecrypt($encryptedOrderId);
            $this->recoverCart->setShouldCancelOrders(false)->execute($orderId);
            /** @var Order $order */
            $order = $this->orderLoader->loadOrderById($orderId);
            if ($order !== null && $order->getState() === Order::STATE_CANCELED) {
                $this->tryVoidTransaction($order);
            }
        } elseif (isset($params['quoteId']) && $params['quoteId'] !== null) {
            $encryptedQuoteId = $params['quoteId'];
            $quoteId = $this->cryptAndCode->decodeAndDecrypt($encryptedQuoteId);
            $session->setQuoteId((int)$quoteId);
            $quote = $this->quoteRepository->get($quoteId);
            $session->replaceQuote($quote);
        }
        $session->setData(\Ebizmarts\SagePaySuite\Model\SessionInterface::PRESAVED_PENDING_ORDER_KEY, null);
        $session->setData(\Ebizmarts\SagePaySuite\Model\SessionInterface::CONVERTING_QUOTE_TO_ORDER, 0);

        if (!empty($params['errorMessage'])) {
            $this->addErrorMessage($params['errorMessage']);
        }

        $this->redirect->redirect($this->response, $this->getRedirectUrl());
        return $this->response;
    }

    /**
     * Allows plugin to set a custom redirect URL (e.g hyva)
     * @return string
     */
    public function getRedirectUrl()
    {
        return "checkout/cart";
    }

    /**
     * Create exception in case CSRF validation failed.
     * Return null if default exception will suffice.
     *
     * @param RequestInterface $request
     *
     * @return InvalidRequestException|null
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * Perform custom request validation.
     * Return null if default validation is needed.
     *
     * @param RequestInterface $request
     *
     * @return bool|null
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    /**
     * @param $errorMessage
     */
    public function addErrorMessage(string $errorMessage)
    {
        $this->messageManager->addErrorMessage(urldecode($errorMessage));
    }

    /**
     * @param PiTransactionResultInterface $transactionDetails
     * @return bool
     */
    private function isSuccessTransaction($transactionDetails)
    {
        return in_array($transactionDetails->getStatusCode(), $this->okStatuses, true);
    }

    /**
     * @param Order $order
     */
    private function tryVoidTransaction($order)
    {
        $payment = $order->getPayment();
        try {
            $transactionId = $payment->getLastTransId();
            /** @var PiTransactionResultInterface $transactionDetails */
            $transactionDetails = $this->piRestApi->transactionDetails($transactionId);
            $this->suiteLogger->sageLog(
                Logger::LOG_REQUEST,
                $transactionDetails,
                [__METHOD__, __LINE__]
            );
            if ($this->isSuccessTransaction($transactionDetails)) {
                if ($this->config->getSagepayPaymentAction() === Config::ACTION_PAYMENT_PI) {
                    /** @var PiInstructionResponse $result */
                    $result = $this->piRestApi->void($transactionId);
                } else {
                    /** @var PiInstructionResponse $result */
                    $result = $this->piRestApi->abort($transactionId);
                }
                $this->suiteLogger->sageLog(
                    Logger::LOG_REQUEST,
                    $result,
                    [__METHOD__, __LINE__]
                );
                $transactionDetails = $this->piRestApi->transactionDetails($transactionId);
            }
            $payment->setAdditionalInformation('statusDetail', $transactionDetails->getStatusDetail());
            $payment->save();
        } catch (ApiException $apiException) {
            $this->suiteLogger->sageLog(
                Logger::LOG_EXCEPTION,
                $apiException->getMessage(),
                [__METHOD__, __LINE__]
            );
            $this->suiteLogger->sageLog(
                Logger::LOG_EXCEPTION,
                $apiException->getTraceAsString(),
                [__METHOD__, __LINE__]
            );
        } catch (\Exception $exception) {
            $this->suiteLogger->sageLog(
                Logger::LOG_EXCEPTION,
                $exception->getMessage(),
                [__METHOD__, __LINE__]
            );
            $this->suiteLogger->sageLog(
                Logger::LOG_EXCEPTION,
                $exception->getTraceAsString(),
                [__METHOD__, __LINE__]
            );
        }
    }

    /**
     * @return RequestInterface
     */
    protected function getRequest()
    {
        return $this->request;
    }
}
