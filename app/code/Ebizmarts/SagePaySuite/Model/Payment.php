<?php

namespace Ebizmarts\SagePaySuite\Model;

use Ebizmarts\SagePaySuite\Model\Config;
use Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultInterface;
use Ebizmarts\SagePaySuite\Model\Api\ApiException;
use Ebizmarts\SagePaySuite\Model\Api\PaymentOperationsInterface;
use Ebizmarts\SagePaySuite\Model\Payment\Refund\Deferred as SagePaySuiteDeferredRefund;
use Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger as SagePayLogger;
use Ebizmarts\SagePaySuite\Model\Api\Reporting;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Model\Order;

class Payment
{
    private const ERROR_MESSAGE = "There was an error %1 Elavon transaction %2: %3";

    /** @var Api\Shared|\Ebizmarts\SagePaySuite\Model\Api\Pi */
    private $api;

    /** @var SagePayLogger */
    private $logger;

    /** @var */
    private $suiteHelper;

    /** @var Config */
    private $config;

    /** @var SagePaySuiteDeferredRefund $sagePaySuiteDeferredRefund */
    private $sagePaySuiteDeferredRefund;

    /** @var Reporting $reportingApi */
    private $reportingApi;

    public function __construct(
        \Ebizmarts\SagePaySuite\Model\Api\Shared $sharedApi,
        SagePayLogger $logger,
        \Ebizmarts\SagePaySuite\Helper\Data $suiteHelper,
        Config $config,
        SagePaySuiteDeferredRefund $sagePaySuiteDeferredRefund,
        Reporting $reportingApi
    ) {
        $this->logger = $logger;
        $this->api = $sharedApi;
        $this->suiteHelper = $suiteHelper;
        $this->config = $config;
        $this->sagePaySuiteDeferredRefund = $sagePaySuiteDeferredRefund;
        $this->reportingApi = $reportingApi;
    }

    public function setApi(PaymentOperationsInterface $apiInstance)
    {
        $this->api = $apiInstance;
    }

    /**
     * @param InfoInterface $payment
     * @param $amount
     * @return $this
     * @throws LocalizedException
     */
    public function capture(InfoInterface $payment, $amount)
    {
        try {
            $transactionId = "-1";
            $action = "with";
            /** @var Order $order */
            $order = $payment->getOrder();

            if ($this->canCaptureAuthorizedTransaction($payment, $order)) {
                $transactionId = $payment->getParentTransactionId();

                $paymentAction = $this->getTransactionPaymentAction($payment);

                $result = [];
                if ($this->isDeferredOrRepeatDeferredAction($paymentAction)) {
                    $action = 'releasing';
                    if ($this->config->getCurrencyConfig() === CONFIG::CURRENCY_SWITCHER) {
                        $amount = $this->calculateAmount($amount, $order);
                    }
                    $result = $this->api->captureDeferredTransaction($transactionId, $amount, $order);
                } elseif ($this->isAuthenticateAction($paymentAction)) {
                    $action = 'authorizing';
                    if ($this->config->getCurrencyConfig() === CONFIG::CURRENCY_SWITCHER) {
                        $amount = $this->calculateAmount($amount, $order);
                    }
                    $result = $this->api->authorizeTransaction($transactionId, $amount, $order);
                }

                if (\is_array($result) && isset($result['data'])) {
                    $this->addAdditionalInformationToTransaction($payment, $result);

                    if (isset($result['data']['VPSTxId'])) {
                        $payment->setTransactionId($this->suiteHelper->removeCurlyBraces($result['data']['VPSTxId']));
                    }
                    $payment->setParentTransactionId($payment->getParentTransactionId());
                } elseif ($result instanceof PiTransactionResultInterface) { //Pi repeat
                    /** @var $result PiTransactionResultInterface */
                    $payment->setTransactionId($result->getTransactionId());
                    $payment->setParentTransactionId($payment->getParentTransactionId());
                }

                if ($order->getPayment()->getMethod() === Config::METHOD_PAYPAL) {
                    $this->updatePaymentAdditionalInformation($order);
                }
            }
        } catch (ApiException $apiException) {
            $this->logger->logException($apiException);
            throw new LocalizedException(
                __(
                    $this->getErrorMessage(),
                    $action,
                    $transactionId,
                    $apiException->getUserMessage()
                )
            );
        } catch (\Exception $e) {
            $this->logger->logException($e);
            throw new LocalizedException(__($this->getErrorMessage(), $action, $transactionId, $e->getMessage()));
        }

        return $this;
    }

    /**
     * @param InfoInterface $payment
     * @param float $amount
     * @return $this
     * @throws LocalizedException
     */
    public function refund(InfoInterface $payment, $amount)
    {
        $order = $payment->getOrder();
        $transactionId = $this->suiteHelper->clearTransactionId($payment->getParentTransactionId());
        $associatedTransactions = $payment->getAdditionalInformation("associatedTransactions");

        try {
            $shouldRefundOneTransaction = $this->sagePaySuiteDeferredRefund->shouldRefundOneTransaction(
                $payment,
                $associatedTransactions,
                $transactionId
            );
            if ($shouldRefundOneTransaction) {
                if ($this->config->getCurrencyConfig() === CONFIG::CURRENCY_SWITCHER) {
                    $amount = $this->calculateAmount($amount, $order);
                }
                $this->tryRefund($payment, $transactionId, $amount);
            } else {
                $this->logAssociatedTransactionRefund($associatedTransactions);
                foreach ($associatedTransactions as $key => $value) {
                    if ($amount > 0 && $value > 0) {
                        $associatedVpsTxId = $this->suiteHelper->clearTransactionId($key);
                        if ($amount <= $value) {
                            $refundAmount = $this->calculateAmountToRefund($order, $amount);
                            $value -= $amount;
                            $amount = 0;
                        } else {
                            $refundAmount = $this->calculateAmountToRefund($order, $value);
                            $amount -= $value;
                            $value = 0;
                        }
                        try {
                            $this->tryRefund($payment, $associatedVpsTxId, $refundAmount);
                            $associatedTransactions[$key] = $value;
                            $payment->setAdditionalInformation(
                                'associatedTransactions',
                                $associatedTransactions
                            )->save();
                        } catch (\Exception $exception) {
                            $this->logRefundError($associatedVpsTxId, $exception->getMessage(), $refundAmount);
                        }
                    }
                }
            }
        } catch (ApiException $apiException) {
            $this->logRefundError($transactionId, $apiException->getUserMessage(), $amount);
            $this->logger->logException($apiException);
            throw new LocalizedException(
                __($this->getErrorMessage(), "refunding", $transactionId, $apiException->getUserMessage())
            );
        } catch (\Exception $e) {
            $this->logRefundError($transactionId, $e->getMessage(), $amount);
            $this->logger->logException($e);
            throw new LocalizedException(__($this->getErrorMessage(), "refunding", $transactionId, $e->getMessage()));
        }

        return $this;
    }

    /**
     * @param InfoInterface $payment
     * @param $transactionId
     * @param $amount
     * @return mixed
     */
    private function tryRefund(InfoInterface $payment, $transactionId, $amount)
    {
        $this->logRefund($transactionId, $amount, $payment->getId());
        $result = $this->api->refundTransaction($transactionId, $amount, $payment->getOrder());

        $this->addAdditionalInformationToTransaction($payment, $result);

        $payment->setIsTransactionClosed(1);
        $payment->setShouldCloseParentTransaction(1);

        return $transactionId;
    }

    /**
     * @param InfoInterface $payment
     * @param string $paymentAction
     * @param \Magento\Framework\DataObject $stateObject
     */
    public function setOrderStateAndStatus($payment, string $paymentAction, $stateObject)
    {
        if ($paymentAction == 'PAYMENT') {
            $this->setPendingPaymentState($stateObject);
        } elseif ($paymentAction == 'DEFERRED' || $paymentAction == 'AUTHENTICATE') {
            if ($payment->getLastTransId() !== null) {
                $stateObject->setState(Order::STATE_NEW);
                $stateObject->setStatus('pending');
            } else {
                $this->setPendingPaymentState($stateObject);
            }
        }
    }

    /**
     * @param $stateObject
     */
    private function setPendingPaymentState($stateObject)
    {
        $stateObject->setState(Order::STATE_PENDING_PAYMENT);
        $stateObject->setStatus('pending_payment');
    }

    /**
     * @param InfoInterface $payment
     * @param $result
     */
    private function addAdditionalInformationToTransaction(InfoInterface $payment, $result)
    {
        if (\is_array($result) && isset($result['data'])) {
            foreach ($result['data'] as $name => $value) {
                $payment->setTransactionAdditionalInfo($name, $value);
            }
        }
    }

    /**
     * @param $paymentAction
     * @return bool
     */
    private function isDeferredOrRepeatDeferredAction($paymentAction)
    {
        return $paymentAction === Config::ACTION_DEFER ||
            $paymentAction === Config::ACTION_REPEAT_DEFERRED ||
            $paymentAction === Config::ACTION_DEFER_PI;
    }

    /**
     * @param $paymentAction
     * @return bool
     */
    private function isAuthenticateAction($paymentAction)
    {
        return $paymentAction == Config::ACTION_AUTHENTICATE;
    }

    /**
     * @param InfoInterface $payment
     * @param $order
     * @return bool
     */
    private function canCaptureAuthorizedTransaction(InfoInterface $payment, $order)
    {
        return $payment->getLastTransId() && $order->getState() != Order::STATE_PENDING_PAYMENT;
    }

    /**
     * @param InfoInterface $payment
     * @return mixed|null|string
     */
    private function getTransactionPaymentAction(InfoInterface $payment)
    {
        $paymentAction = $this->config->getSagepayPaymentAction();
        if ($payment->getAdditionalInformation('paymentAction')) {
            $paymentAction = $payment->getAdditionalInformation('paymentAction');
        }

        return $paymentAction;
    }

    /**
     * @param $amount
     * @param $order
     * @return float|int
     */
    public function calculateAmount($amount, $order)
    {
        if ($amount == $order->getBaseGrandTotal()) {
            $amount = $order->getGrandTotal();
        } else {
            $rate = $order->getBaseToOrderRate();
            $currencySwitcherAmount = $amount * $rate;
            $amount = $currencySwitcherAmount;
        }
        return $amount;
    }

    private function getErrorMessage()
    {
        return self::ERROR_MESSAGE;
    }

    /**
     * @param Order $order
     * @param float $baseAmount
     * @return float|int
     */
    private function calculateAmountToRefund($order, $baseAmount)
    {
        if ($this->config->getCurrencyConfig() === CONFIG::CURRENCY_SWITCHER) {
            $baseAmount = $this->calculateAmount($baseAmount, $order);
        }

        return $baseAmount;
    }

    /**
     * @param string $vpsTxId
     * @param string $refundAmount
     * @param string $paymentId
     * @return void
     */
    private function logRefund($vpsTxId, $refundAmount, $paymentId)
    {
        $message = 'Transaction to refund: ';
        $message .= $vpsTxId;
        $message .= ' - payment id: ';
        $message .= $paymentId;
        $message .= ' - refund amount: ';
        $message .= $refundAmount;
        $this->logger->sageLog(
            SagePayLogger::LOG_REFUND,
            $message,
            [__METHOD__, __LINE__]
        );
    }

    /**
     * @param array $associatedTransactions
     * @return void
     */
    private function logAssociatedTransactionRefund($associatedTransactions)
    {
        $this->logger->sageLog(
            SagePayLogger::LOG_REFUND,
            $associatedTransactions,
            [__METHOD__, __LINE__]
        );
    }

    /**
     * @param string $vpsTxId
     * @param string $errorMessage
     * @param string $refundAmount
     * @return void
     */
    private function logRefundError($vpsTxId, $errorMessage, $refundAmount)
    {
        $message = 'There was an error refunding Opayo transaction: ';
        $message .= $vpsTxId;
        $message .= ' - refundAmount: ';
        $message .= $refundAmount;
        $message .= ' - error: ';
        $message .= $errorMessage;
        $this->logger->sageLog(
            SagePayLogger::LOG_REFUND,
            $message,
            [__METHOD__, __LINE__]
        );
    }

    private function updatePaymentAdditionalInformation($order)
    {

        $payment = $order->getPayment();

        $transactionIdDirty = $payment->getLastTransId();

        $transactionId = $this->suiteHelper->clearTransactionId($transactionIdDirty);

        if ($transactionId != null) {
            $transactionDetails = $this->reportingApi
                ->getTransactionDetailsByVpstxid($transactionId, $order->getStoreId());
        } else {
            $vendorTxCode = $payment->getAdditionalInformation("vendorTxCode");
            $transactionDetails = $this->reportingApi
                ->getTransactionDetailsByVendorTxCode($vendorTxCode, $order->getStoreId());
        }

        if ($this->issetTransactionDetails($transactionDetails)) {
            $payment->setLastTransId((string)$transactionDetails->vpstxid);
            $payment->setAdditionalInformation('vendorTxCode', (string)$transactionDetails->vendortxcode);
            $payment->setAdditionalInformation('statusDetail', (string)$transactionDetails->status);

            if (isset($transactionDetails->securitykey)) {
                $payment->setAdditionalInformation('securityKey', (string)$transactionDetails->securitykey);
            }

            if (isset($transactionDetails->threedresult)) {
                $payment->setAdditionalInformation('threeDStatus', (string)$transactionDetails->threedresult);
            }
            $payment->save();
        }
    }
    /**
     * @return bool
     */
    private function issetTransactionDetails($transactionDetails)
    {
        return isset($transactionDetails->vpstxid) && isset($transactionDetails->vendortxcode) &&
            isset($transactionDetails->status);
    }

    private function shouldHoldTransaction($payment)
    {
        if (!$this->config->getAdvancedValue('hold_order')) {
            return;
        }

        $billingAddressStreet = $payment->getOrder()->getBillingAddress()->getStreet();
        $shippingAddressStreet = $payment->getOrder()->getShippingAddress()->getStreet();
        return $billingAddressStreet != $shippingAddressStreet;
    }
}
