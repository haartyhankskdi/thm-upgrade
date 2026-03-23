<?php
/**
 * Copyright © 2018 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Model\PiRequestManagement;

use Ebizmarts\SagePaySuite\Api\Data\PiResultInterface;
use Ebizmarts\SagePaySuite\Helper\Checkout;
use Ebizmarts\SagePaySuite\Helper\Data;
use Ebizmarts\SagePaySuite\Model\Api\ApiException;
use Ebizmarts\SagePaySuite\Model\Api\PIRest;
use Ebizmarts\SagePaySuite\Model\Config;
use Ebizmarts\SagePaySuite\Model\Config\ClosedForActionFactory;
use Ebizmarts\SagePaySuite\Model\Config\SagePayCardType;
use Ebizmarts\SagePaySuite\Model\CryptAndCodeData;
use Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger;
use Ebizmarts\SagePaySuite\Model\PiRequest;
use Ebizmarts\SagePaySuite\Model\Token\VaultDetailsHandler;
use Magento\Checkout\Model\Session;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Validator\Exception as ValidatorException;
use Magento\Quote\Model\QuoteValidator;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Payment\TransactionFactory;
use Magento\Sales\Api\OrderPaymentRepositoryInterfaceFactory;
use Magento\Sales\Api\OrderPaymentRepositoryInterface;
use Ebizmarts\SagePaySuite\Model\AcsUrl;

class EcommerceManagement extends RequestManagement
{
    private const ERROR_MESSAGE_INVALID_CHARACTERS = 'invalid characters';

    /** @var Session */
    private $checkoutSession;

    /** @var Logger */
    private $suiteLogger;

    /** @var ClosedForActionFactory  */
    private $actionFactory;

    /** @var TransactionFactory */
    private $transactionFactory;

    /** @var QuoteValidator */
    private $quoteValidator;

    /** @var InvoiceSender */
    private $invoiceEmailSender;

    /** @var Config */
    private $config;

    /** @var CryptAndCodeData */
    private $cryptAndCode;

    /** @var VaultDetailsHandler */
    private $vaultDetailsHandler;

    /** @var ManagerInterface */
    private $eventManager;

    /** @var UrlInterface */
    private $url;

    /** @var string */
    private $transactionId;

    /** @var string */
    private $errorMessage;

    /** @var OrderPaymentRepositoryInterfaceFactory */
    private $orderPaymentRepositoryInterfaceFactory;

    /** @var AcsUrl */
    private $acsUrl;

    /**
     * EcommerceManagement constructor.
     * @param Checkout $checkoutHelper
     * @param PIRest $piRestApi
     * @param SagePayCardType $ccConvert
     * @param PiRequest $piRequest
     * @param Data $suiteHelper
     * @param PiResultInterface $result
     * @param Session $checkoutSession
     * @param Logger $sagePaySuiteLogger
     * @param ClosedForActionFactory $actionFactory
     * @param TransactionFactory $transactionFactory
     * @param QuoteValidator $quoteValidator
     * @param InvoiceSender $invoiceEmailSender
     * @param Config $config
     * @param CryptAndCodeData $cryptAndCode
     * @param VaultDetailsHandler $vaultDetailsHandler
     * @param ManagerInterface $eventManager
     * @param UrlInterface $url
     * @param OrderPaymentRepositoryInterfaceFactory $orderPaymentRepositoryInterfaceFactory
     * @param AcsUrl $acsUrl
     */
    public function __construct(
        Checkout $checkoutHelper,
        PIRest $piRestApi,
        SagePayCardType $ccConvert,
        PiRequest $piRequest,
        Data $suiteHelper,
        PiResultInterface $result,
        Session $checkoutSession,
        Logger $sagePaySuiteLogger,
        ClosedForActionFactory $actionFactory,
        TransactionFactory $transactionFactory,
        QuoteValidator $quoteValidator,
        InvoiceSender $invoiceEmailSender,
        Config $config,
        CryptAndCodeData $cryptAndCode,
        VaultDetailsHandler $vaultDetailsHandler,
        ManagerInterface $eventManager,
        UrlInterface $url,
        OrderPaymentRepositoryInterfaceFactory $orderPaymentRepositoryInterfaceFactory,
        AcsUrl $acsUrl
    ) {
        parent::__construct(
            $checkoutHelper,
            $piRestApi,
            $ccConvert,
            $piRequest,
            $suiteHelper,
            $result
        );
        $this->checkoutSession     = $checkoutSession;
        $this->suiteLogger         = $sagePaySuiteLogger;
        $this->actionFactory       = $actionFactory;
        $this->transactionFactory  = $transactionFactory;
        $this->quoteValidator      = $quoteValidator;
        $this->invoiceEmailSender  = $invoiceEmailSender;
        $this->config              = $config;
        $this->cryptAndCode        = $cryptAndCode;
        $this->vaultDetailsHandler = $vaultDetailsHandler;
        $this->eventManager        = $eventManager;
        $this->url                 = $url;
        $this->orderPaymentRepositoryInterfaceFactory = $orderPaymentRepositoryInterfaceFactory;
        $this->acsUrl              = $acsUrl;
    }

    public function placeOrder()
    {
        try {
            $this->quoteValidator->validateBeforeSubmit($this->getQuote());
            $this->tryToChargeCustomerAndCreateOrder();
        } catch (ApiException $apiException) {
            $this->transactionId = $apiException->getTransactionId();
            $this->tryToVoidTransactionLogErrorAndUpdateResult($apiException);
        } catch (LocalizedException $quoteException) {
            $this->tryToVoidTransactionLogErrorAndUpdateResult($quoteException);
        } catch (\Exception $e) {
            $this->tryToVoidTransactionLogErrorAndUpdateResult($e);
        }

        return $this->getResult();
    }

    private function tryToChargeCustomerAndCreateOrder()
    {
        //send request payment to opayo
        $this->pay();

        $this->processPayment();

        //save order with pending payment
        $quote = $this->getQuote();
        $order = $this->getCheckoutHelper()->placeOrder($quote);

        if ($order !== null) {
            //set pre-saved order flag in checkout session
            $this->checkoutSession->setData(
                \Ebizmarts\SagePaySuite\Model\SessionInterface::PRESAVED_PENDING_ORDER_KEY,
                $order->getId()
            );
            $this->checkoutSession->setData(
                \Ebizmarts\SagePaySuite\Model\SessionInterface::CONVERTING_QUOTE_TO_ORDER,
                1
            );

            $payment = $order->getPayment();
            if ($payment->getEntityId()) {
                /** @var OrderPaymentRepositoryInterface $orderPaymentRepositoryInterface */
                $orderPaymentRepositoryInterface = $this->orderPaymentRepositoryInterfaceFactory->create();
                $payment->setTransactionId($this->getPayResult()->getTransactionId());
                $payment->setLastTransId($this->getPayResult()->getTransactionId());
                $payment = $orderPaymentRepositoryInterface->save($payment);

                $this->eventManager->dispatch('checkout_submit_all_after', ['order' => $order, 'quote' => $quote]);

                $this->suiteLogger->debugLog($payment->getData(), [__LINE__, __METHOD__]);

                $this->createInvoiceForSuccessPayment($payment, $order);
            } else {
                throw new NotFoundException(__('Unable to get Elavon order payment'));
            }
        } else {
            throw new ValidatorException(__('Unable to save Elavon order'));
        }

        $this->getResult()->setSuccess(true);
        $this->getResult()->setTransactionId($this->getPayResult()->getTransactionId());
        $this->getResult()->setStatus($this->getPayResult()->getStatus());

        //additional details required for callback URL
        $orderId = $order->getId();
        $orderId = $this->encryptAndEncode($orderId);
        $this->getResult()->setOrderId($orderId);

        $quoteId = $this->getQuote()->getId();
        $quoteId = $this->encryptAndEncode($quoteId);
        $this->getResult()->setQuoteId($quoteId);

        if ($this->isThreeDResponse()) {
            $this->getResult()->setCreq($this->getPayResult()->getCReq());
            $this->getResult()->setAcsUrl($this->getPayResult()->getAcsUrl());
            $this->acsUrl->saveAcsUrlDomain($this->getPayResult()->getAcsUrl());
        } else {
            if ($this->getRequestData()->getSaveToken()) {
                $this->vaultDetailsHandler->saveToken(
                    $payment,
                    $order->getCustomerId(),
                    $this->getRequestData()->getCardIdentifier()
                );
            }
            $this->checkoutSession->setData(
                \Ebizmarts\SagePaySuite\Model\SessionInterface::CONVERTING_QUOTE_TO_ORDER,
                0
            );
        }
    }

    /**
     * @param $payment
     * @param $order
     */
    private function createInvoiceForSuccessPayment($payment, $order)
    {
        //invoice
        $statusCode = $this->getPayResult()->getStatusCode();
        $this->suiteLogger->debugLog("StatusCode: " . $statusCode, [__LINE__, __METHOD__]);
        if ($statusCode === Config::SUCCESS_STATUS) {
            $request = $this->getRequest();
            $sagePayPaymentAction = $request['transactionType'];
            $this->suiteLogger->debugLog("PaymentAction: " . $sagePayPaymentAction, [__LINE__, __METHOD__]);
            if ($sagePayPaymentAction === Config::ACTION_PAYMENT_PI) {
                $payment->getMethodInstance()->markAsInitialized();
            }
            $order->place()->save();

            $this->getCheckoutHelper()->sendOrderEmail($order);
            $this->sendInvoiceNotification($order);

            if ($sagePayPaymentAction === Config::ACTION_DEFER_PI) {
                /** @var \Ebizmarts\SagePaySuite\Model\Config\ClosedForAction $actionClosed */
                $actionClosed = $this->actionFactory->create(['paymentAction' => $sagePayPaymentAction]);
                list($action, $closed) = $actionClosed->getActionClosedForPaymentAction();

                /** @var \Magento\Sales\Model\Order\Payment\Transaction $transaction */
                $transaction = $this->transactionFactory->create();
                $transaction->setOrderPaymentObject($payment);
                $transaction->setTxnId($this->getPayResult()->getTransactionId());
                $transaction->setOrderId($order->getEntityId());
                $transaction->setTxnType($action);
                $transaction->setPaymentId($payment->getId());
                $transaction->setIsClosed($closed);
                $transaction->save();
            }

            //prepare session to success page
            $this->checkoutSession->clearHelperData();
            //set last successful quote
            $this->checkoutSession->setLastQuoteId($this->getQuote()->getId());
            $this->checkoutSession->setLastSuccessQuoteId($this->getQuote()->getId());
            $this->checkoutSession->setLastOrderId($order->getId());
            $this->checkoutSession->setLastRealOrderId($order->getIncrementId());
            $this->checkoutSession->setLastOrderStatus($order->getStatus());
        }
    }

    /**
     * @param $exceptionObject
     */
    private function tryToVoidTransactionLogErrorAndUpdateResult($exceptionObject)
    {
        $this->errorMessage = $exceptionObject->getMessage();
        $this->suiteLogger->logException($exceptionObject, [__METHOD__, __LINE__]);
        $this->tryToVoidTransactionAndUpdateResult();
    }

    /**
     * @param string $errorMessage
     */
    public function tryToVoidTransactionAndUpdateResult($errorMessage = null)
    {
        $errorMessage = ($errorMessage === null) ? $this->errorMessage : $errorMessage;
        
        if (!empty($errorMessage)) {
            $this->getResult()->setErrorMessage(
                __("Something went wrong, please try again or ask the store for help. Error details: %1", $errorMessage)
            );
        } else {
            $this->getResult()->setErrorMessage(
                __("Something went wrong, please try again or ask the store for help.")
            );
        }
        
        $this->getResult()->setSuccess(false);

        if ($this->getPayResult() !== null && $this->isPaymentSuccessful()) {
            try {
                $this->getPiRestApi()->void($this->getPayResult()->getTransactionId());
            } catch (ApiException $apiException) {
                $this->suiteLogger->logException($apiException);
            }
        } elseif (strpos($errorMessage, self::ERROR_MESSAGE_INVALID_CHARACTERS) !== false) {
            $failedTransaction = $this->url->getUrl('sagepaysuite/pi/failure', [
                '_nosid' => true,
                '_secure' => true,
                '_store'  => $this->getQuote()->getStoreId()
            ]);
            $encryptedQuoteId = $this->encryptAndEncode($this->getQuote()->getId());
            $failedTransaction .=
                '?quoteId=' . $encryptedQuoteId .
                '&errorMessage=' . $errorMessage;
            $this->getResult()->setRedirectToFailureUrl($failedTransaction);
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
     * @return bool
     */
    private function isThreeDResponse()
    {
        return $this->getPayResult()->getStatusCode() == Config::AUTH3D_V2_REQUIRED_STATUS;
    }

    public function isPaymentSuccessful()
    {
        return $this->getPayResult()->getStatusCode() == Config::SUCCESS_STATUS;
    }

    /**
     * @param $data
     * @return string
     */
    public function encryptAndEncode($data)
    {
        return $this->cryptAndCode->encryptAndEncode($data);
    }

    public function getTransactionId()
    {
        return $this->transactionId;
    }

    public function getErrorMessage()
    {
        return $this->errorMessage;
    }
}
