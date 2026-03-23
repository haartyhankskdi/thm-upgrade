<?php

namespace Ebizmarts\SagePaySuite\Model\PiRequestManagement;

use Ebizmarts\SagePaySuite\Api\Data\PiResultInterface;
use Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultFactory;
use Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultInterface;
use Ebizmarts\SagePaySuite\Helper\Checkout;
use Ebizmarts\SagePaySuite\Helper\Data;
use Ebizmarts\SagePaySuite\Model\Api\ApiException;
use Ebizmarts\SagePaySuite\Model\Api\PIRest;
use Ebizmarts\SagePaySuite\Model\Config;
use Ebizmarts\SagePaySuite\Model\Config\ClosedForActionFactory;
use Ebizmarts\SagePaySuite\Model\Config\SagePayCardType;
use Ebizmarts\SagePaySuite\Model\CryptAndCodeData;
use Ebizmarts\SagePaySuite\Model\ObjectLoader\OrderLoader;
use Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger;
use Ebizmarts\SagePaySuite\Model\PiRequest;
use Ebizmarts\SagePaySuite\Model\Token\VaultDetailsHandler;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Validator\Exception as ValidatorException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Payment\TransactionFactory;

class ThreeDSecureCallbackManagement extends RequestManagement
{
    private const NUM_OF_ATTEMPTS = 5;
    private const RETRY_INTERVAL = 6000000;
    private const ERROR_OPERATION_NOT_ALLOWED = 'Operation not allowed';
    private const INVALID_3D_AUTH_MESSAGE = 'Invalid 3D secure authentication, no status detail found.';

    /** @var Session */
    private $checkoutSession;

    /** @var RequestInterface */
    private $httpRequest;

    /** @var \Magento\Sales\Model\Order */
    private $order;

    /** @var TransactionFactory */
    private $transactionFactory;

    /** @var PiTransactionResultFactory */
    private $payResultFactory;

    /** @var ClosedForActionFactory  */
    private $actionFactory;

    /** @var OrderRepositoryInterface  */
    private $orderRepository;

    /** @var InvoiceSender */
    private $invoiceEmailSender;

    /** @var Config */
    private $config;

    /** @var CryptAndCodeData */
    private $cryptAndCode;

    /** @var OrderLoader */
    private $orderLoader;

    /** @var VaultDetailsHandler */
    private $vaultDetailsHandler;

    /** @var Logger */
    private $suiteLogger;

    /**
     * ThreeDSecureCallbackManagement constructor.
     * @param Checkout $checkoutHelper
     * @param PIRest $piRestApi
     * @param SagePayCardType $ccConvert
     * @param PiRequest $piRequest
     * @param Data $suiteHelper
     * @param PiResultInterface $result
     * @param Session $checkoutSession
     * @param RequestInterface $httpRequest
     * @param TransactionFactory $transactionFactory
     * @param PiTransactionResultFactory $payResultFactory
     * @param ClosedForActionFactory $actionFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param InvoiceSender $invoiceEmailSender
     * @param Config $config
     * @param CryptAndCodeData $cryptAndCode
     * @param VaultDetailsHandler $vaultDetailsHandler
     * @param OrderLoader $orderLoader
     * @param Logger $suiteLogger
     */
    public function __construct(
        Checkout $checkoutHelper,
        PIRest $piRestApi,
        SagePayCardType $ccConvert,
        PiRequest $piRequest,
        Data $suiteHelper,
        PiResultInterface $result,
        Session $checkoutSession,
        RequestInterface $httpRequest,
        TransactionFactory $transactionFactory,
        PiTransactionResultFactory $payResultFactory,
        ClosedForActionFactory $actionFactory,
        OrderRepositoryInterface $orderRepository,
        InvoiceSender $invoiceEmailSender,
        Config $config,
        CryptAndCodeData $cryptAndCode,
        VaultDetailsHandler $vaultDetailsHandler,
        OrderLoader $orderLoader,
        Logger $suiteLogger
    ) {
        parent::__construct(
            $checkoutHelper,
            $piRestApi,
            $ccConvert,
            $piRequest,
            $suiteHelper,
            $result
        );

        $this->httpRequest         = $httpRequest;
        $this->checkoutSession     = $checkoutSession;
        $this->transactionFactory  = $transactionFactory;
        $this->payResultFactory    = $payResultFactory;
        $this->actionFactory       = $actionFactory;
        $this->orderRepository     = $orderRepository;
        $this->invoiceEmailSender  = $invoiceEmailSender;
        $this->config              = $config;
        $this->cryptAndCode        = $cryptAndCode;
        $this->vaultDetailsHandler = $vaultDetailsHandler;
        $this->orderLoader        = $orderLoader;
        $this->suiteLogger         = $suiteLogger;
    }

    public function getPayment()
    {
        return $this->order->getPayment();
    }

    /**
     * @return PiTransactionResultInterface
     */
    public function pay($isMoto = false)
    {
        $payResult = $this->payResultFactory->create();
        $this->setPayResult($payResult);
        $cres = $this->getRequestData()->getCres();

        /** @var \Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultThreeD $submit3Dv2Result */
        $submit3DResult = $this->getPiRestApi()->submit3Dv2(
            $cres,
            $this->getRequestData()->getTransactionId()
        );

        $this->getPayResult()->setStatus($submit3DResult->getStatus());

        return $this->getPayResult();
    }

    /**
     * @param bool $isMultishipping
     * @param array|null $orderIds
     * @return PiResultInterface
     * @throws ValidatorException
     * @throws ApiException
     */
    public function placeOrder($isMultishipping = false, $orderIds = null)
    {
        $payResult = $this->pay();
        $paymentStatus = $payResult->getStatus();

        if ($paymentStatus == self::ERROR_OPERATION_NOT_ALLOWED) {
            $this->getResult()->setErrorMessage(self::ERROR_OPERATION_NOT_ALLOWED);
        } elseif ($paymentStatus == "Ok" || $paymentStatus == "Authenticated") {
            $transactionDetailsResult = $this->retrieveTransactionDetails();
            $this->suiteLogger->debugLog($transactionDetailsResult, [__LINE__, __METHOD__]);

            if ($isMultishipping == false) {
                $this->confirmPayment($transactionDetailsResult);
                if ($this->getRequestData()->getSaveToken()) {
                    $this->vaultDetailsHandler->saveToken(
                        $this->getPayment(),
                        $this->order->getCustomerId(),
                        $transactionDetailsResult->getPaymentMethod()->getCard()->getCardIdentifier()
                    );
                }
            } else {
                foreach ($orderIds as $orderId) {
                    $this->order = $this->orderLoader->loadOrderById($orderId);
                    $this->confirmPaymentMultishipping($transactionDetailsResult, $orderId);
                }
            }

            //remove order pre-saved flag from checkout
            $this->checkoutSession->setData(
                \Ebizmarts\SagePaySuite\Model\SessionInterface::PRESAVED_PENDING_ORDER_KEY,
                null
            );
        } else {
            $transactionDetailsResult = $this->retrieveTransactionDetails();
            if ($transactionDetailsResult->getStatusDetail()) {
                $this->getResult()->setErrorMessage($transactionDetailsResult->getStatusDetail());
            } else {
                $this->getResult()->setErrorMessage(self::INVALID_3D_AUTH_MESSAGE);
            }
        }

        return $this->getResult();
    }

    /**
     * @return \Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResult
     */
    private function retrieveTransactionDetails()
    {
        $attempts = 0;
        $transactionDetailsResult = null;

        $vpsTxId = $this->getRequestData()->getTransactionId();
        $this->suiteLogger->debugLog("VPSTxId: " . $vpsTxId, [__LINE__, __METHOD__]);

        do {
            try {
                /** @var \Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResult $transactionDetailsResult */
                $transactionDetailsResult = $this->getPiRestApi()->transactionDetails($vpsTxId);
            } catch (ApiException $e) {
                $attempts++;
                usleep(self::RETRY_INTERVAL);
                continue;
            }
        } while ($attempts < self::NUM_OF_ATTEMPTS && $transactionDetailsResult === null);

        if (null === $transactionDetailsResult) {
            $this->getPiRestApi()->void($vpsTxId);
            throw new \LogicException("Could not retrieve transaction details");
        }

        return $transactionDetailsResult;
    }

    /**
     * @param PiTransactionResultInterface $response
     * @throws ValidatorException
     */
    private function confirmPayment(PiTransactionResultInterface $response)
    {
        $quoteId = $this->getQuoteIdFromParams();
        $statusCode = $response->getStatusCode();
        $this->suiteLogger->debugLog("StatusCode: " . $statusCode, [__LINE__, __METHOD__]);

        if ($statusCode === Config::SUCCESS_STATUS) {
            $orderId = $this->httpRequest->getParam("orderId");
            $orderId = $this->decodeAndDecrypt($orderId);
            $this->order = $this->orderLoader->loadOrderById($orderId);
            $this->suiteLogger->debugLog("orderId: " . $orderId . " quoteId: " . $quoteId, [__LINE__, __METHOD__]);

            if ($this->order !== null) {
                $this->suiteLogger->debugLog($this->order->getData(), [__LINE__, __METHOD__]);
                $this->getPayResult()->setPaymentMethod($response->getPaymentMethod());
                $this->getPayResult()->setStatusDetail($response->getStatusDetail());
                $this->getPayResult()->setStatusCode($statusCode);
                $this->getPayResult()->setThreeDSecure($response->getThreeDSecure());
                $this->getPayResult()->setTransactionId($response->getTransactionId());
                $this->getPayResult()->setAvsCvcCheck($response->getAvsCvcCheck());
                $this->getPayResult()->setTxAuthNo($response->getTxAuthNo());
                $this->getPayResult()->setRetrievalReference($response->getRetrievalReference());

                $this->processPayment();

                $payment = $this->getPayment();
                $payment->setTransactionId($this->getPayResult()->getTransactionId());
                $payment->setLastTransId($this->getPayResult()->getTransactionId());
                $payment->save();
                $this->suiteLogger->debugLog($payment->getData(), [__LINE__, __METHOD__]);

                $sagePayPaymentAction = $this->getRequestData()->getPaymentAction();
                $this->suiteLogger->debugLog("SagePayPaymentAction: " . $sagePayPaymentAction, [__LINE__, __METHOD__]);

                if ($this->order->getState() != Order::STATE_CANCELED || $this->isTransactionOkOnOpayo()) {
                    //invoice
                    if ($sagePayPaymentAction === Config::ACTION_PAYMENT_PI) {
                        $payment->getMethodInstance()->markAsInitialized();
                    }

                    $this->order->place();

                    $this->orderRepository->save($this->order);

                    //send email
                    $this->getCheckoutHelper()->sendOrderEmail($this->order);
                    $this->sendInvoiceNotification($this->order);

                    if ($sagePayPaymentAction === Config::ACTION_DEFER_PI) {
                        /** @var \Ebizmarts\SagePaySuite\Model\Config\ClosedForAction $actionClosed */
                        $this->_handleDeferTransaction($sagePayPaymentAction, $payment);
                    }

                    //update invoice transaction id
                    if ($sagePayPaymentAction === Config::ACTION_PAYMENT_PI) {
                        $this->order->getInvoiceCollection()->setDataToAll(
                            'transaction_id',
                            $payment->getLastTransId()
                        )->save();
                    }

                    //prepare session to success page
                    $this->checkoutSession->clearHelperData();
                    $this->checkoutSession->setLastQuoteId($quoteId);
                    $this->checkoutSession->setLastSuccessQuoteId($quoteId);
                    $this->checkoutSession->setLastOrderId($this->order->getId());
                    $this->checkoutSession->setLastRealOrderId($this->order->getIncrementId());
                    $this->checkoutSession->setLastOrderStatus($this->order->getStatus());
                } else {
                    throw new ValidatorException(__('Can\'t process cancelled order'));
                }
            } else {
                throw new ValidatorException(__('Unable to save Elavon order'));
            }
        } else {
            throw new ValidatorException(__('Invalid Elavon response: %1', $response->getStatusDetail()));
        }
    }

    /**
     * @param PiTransactionResultInterface $response
     * @param int $orderId
     * @throws ValidatorException
     */
    private function confirmPaymentMultishipping(PiTransactionResultInterface $response, $orderId)
    {
        if ($response->getStatusCode() === Config::SUCCESS_STATUS) {
            $this->order = $this->orderLoader->loadOrderById($orderId);

            if ($this->order !== null) {
                $this->getPayResult()->setPaymentMethod($response->getPaymentMethod() ?? null);
                $this->getPayResult()->setStatusDetail($response->getStatusDetail() ?? null);
                $this->getPayResult()->setStatusCode($response->getStatusCode() ?? null);
                $this->getPayResult()->setThreeDSecure($response->getThreeDSecure() ?? null);
                $this->getPayResult()->setTransactionId($response->getTransactionId() ?: "");
                $this->getPayResult()->setAvsCvcCheck($response->getAvsCvcCheck() ?: "");
                $this->getPayResult()->setTxAuthNo($response->getTxAuthNo() ?: "");
                $this->getPayResult()->setRetrievalReference($response->getRetrievalReference() ?: "");

                $this->processPayment();

                $payment = $this->getPayment();
                $payment->setTransactionId($this->getPayResult()->getTransactionId());
                $payment->setLastTransId($this->getPayResult()->getTransactionId());
                $payment->save();

                $sagePayPaymentAction = $this->getRequestData()->getPaymentAction();

                //invoice
                if ($sagePayPaymentAction === Config::ACTION_PAYMENT_PI) {
                    $payment->getMethodInstance()->markAsInitialized();
                }
                $this->order->place();

                $this->orderRepository->save($this->order);

                //send email
                $this->getCheckoutHelper()->sendOrderEmail($this->order);
                $this->sendInvoiceNotification($this->order);

                if ($sagePayPaymentAction === Config::ACTION_DEFER_PI) {
                    $this->_handleDeferTransaction($sagePayPaymentAction, $payment);
                }

                //update invoice transaction id
                if ($sagePayPaymentAction === Config::ACTION_PAYMENT_PI) {
                    $this->order->getInvoiceCollection()->setDataToAll(
                        'transaction_id',
                        $payment->getLastTransId()
                    )->save();
                }

                //prepare session to success page
                $this->checkoutSession->clearHelperData();
                $this->checkoutSession->setLastOrderId($this->order->getId());
                $this->checkoutSession->setLastRealOrderId($this->order->getIncrementId());
                $this->checkoutSession->setLastOrderStatus($this->order->getStatus());
            } else {
                throw new ValidatorException(__('Unable to save Elavon order'));
            }
        } else {
            throw new ValidatorException(__('Invalid Elavon response: %1', $response->getStatusDetail()));
        }
    }

    public function sendInvoiceNotification($order)
    {
        if ($this->invoiceConfirmationIsEnable() && $this->paymentActionIsCapture()) {
            $invoices = $order->getInvoiceCollection();
            if ($invoices->count() > 0) {
                $this->invoiceEmailSender->send($invoices->getFirstItem());
            }
        }
    }

    /**
     * @return bool
     */
    private function paymentActionIsCapture()
    {
        $sagePayPaymentAction = $this->config->getSagepayPaymentAction();
        return $sagePayPaymentAction === Config::ACTION_PAYMENT_PI;
    }

    /**
     * @return bool
     */
    private function invoiceConfirmationIsEnable()
    {
        return (string)$this->config->getInvoiceConfirmationNotification() === "1";
    }

    /**
     * @param $data
     * @return string
     */
    public function decodeAndDecrypt($data)
    {
        return $this->cryptAndCode->decodeAndDecrypt($data);
    }

    /**
     * @param $sagePayPaymentAction
     * @param $payment
     * @throws \Exception
     */
    private function _handleDeferTransaction($sagePayPaymentAction, $payment)
    {
        /** @var \Ebizmarts\SagePaySuite\Model\Config\ClosedForAction $actionClosed */
        $actionClosed = $this->actionFactory->create(['paymentAction' => $sagePayPaymentAction]);
        list($action, $closed) = $actionClosed->getActionClosedForPaymentAction();

        /** @var \Magento\Sales\Model\Order\Payment\Transaction $transaction */
        $transaction = $this->transactionFactory->create();
        $transaction->setOrderPaymentObject($payment);
        $transaction->setTxnId($this->getPayResult()->getTransactionId());
        $transaction->setOrderId($this->order->getEntityId());
        $transaction->setTxnType($action);
        $transaction->setPaymentId($payment->getId());
        $transaction->setIsClosed($closed);
        $transaction->save();
    }

    /**
     * @return int
     */
    public function getQuoteIdFromParams()
    {
        $quoteId = $this->httpRequest->getParam("quoteId");
        $quoteId = $this->decodeAndDecrypt($quoteId);
        return (int)$quoteId;
    }

    /**
     * @return bool
     */
    private function isTransactionOkOnOpayo()
    {
        $okStatuses = [
            Config::SUCCESS_STATUS,
            Config::AUTH3D_V2_REQUIRED_STATUS
        ];
        try {
            $transactionDetails = $this->retrieveTransactionDetails();
            return in_array($transactionDetails->getStatusCode(), $okStatuses, true);
        } catch (\Exception $e) {
            $message = "Error getting details for transaction, skipping validation\n".
                $e->getMessage();
            $this->suiteLogger->sageLog(Logger::LOG_EXCEPTION, $message, [__LINE__, __METHOD__]);
            return true;
        }
    }
}
