<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Helper;

use Ebizmarts\SagePaySuite\Api\SagePayData\FraudScreenResponseInterface;
use Ebizmarts\SagePaySuite\Model\Api\Reporting;
use Ebizmarts\SagePaySuite\Model\Config;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Store\Model\Store;

class Fraud extends \Magento\Framework\App\Helper\AbstractHelper
{
    public const FSG = 'FSG';
    public const RED = 'ReD';

    /**
     * @var Config
     */
    private $_config;

    /**
     * @var TransportBuilder
     */
    private $_mailTransportBuilder;

    /**
     * \Ebizmarts\SagePaySuite\Model\Api\Reporting
     */
    private $_reportingApi;

    /**
     * @var InvoiceService
     */
    private $invoiceServiceFactory;

    /**
     * TransactionFactory
     *
     * @var \Magento\Framework\DB\TransactionFactory
     */
    private $transactionFactory;

    /**
     * Fraud constructor.
     * @param Context $context
     * @param Config $config
     * @param TransportBuilder $mailTransportBuilder
     * @param Reporting $reportingApi
     * @param \Magento\Framework\DB\TransactionFactory $transactionFactory
     * @param InvoiceService $invoiceService
     */
    public function __construct(
        Context $context,
        Config $config,
        TransportBuilder $mailTransportBuilder,
        Reporting $reportingApi,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Magento\Sales\Model\Service\InvoiceServiceFactory $invoiceService
    ) {
        parent::__construct($context);
        $this->_config               = $config;
        $this->_mailTransportBuilder = $mailTransportBuilder;
        $this->_reportingApi         = $reportingApi;
        $this->invoiceServiceFactory = $invoiceService;
        $this->transactionFactory    = $transactionFactory;
    }

    /**
     * @param $transaction
     * @param $payment
     * @return array
     */
    public function processFraudInformation($transaction, $payment)
    {
        $sagepayVpsTxId = $transaction->getTxnId();

        $logData = ["VPSTxId" => $sagepayVpsTxId];

        //flag test transactions (no actions taken with test orders)
        if ($payment->getAdditionalInformation("mode") &&
            $payment->getAdditionalInformation("mode") == Config::MODE_TEST
        ) {
            /**
             *  TEST TRANSACTION
             */

            $transaction->setSagepaysuiteFraudCheck(1);
            $transaction->save();
            $logData["Action"] = "Marked as TEST";
        } else {

            /**
             * LIVE TRANSACTION
             */

            //get transaction data from sagepay
            /** @var FraudScreenResponseInterface $response */
            $response = $this->_reportingApi->getFraudScreenDetail($sagepayVpsTxId, $payment->getOrder()->getStoreId());

            if ($response->getErrorCode() == "0000") {
                if ($this->fraudCheckAvailable($response)) {
                    //mark payment as fraud
                    if ($this->transactionIsFraud($response)) {
                        $payment->setIsFraudDetected(true);
                        $payment->getOrder()->setStatus(Order::STATUS_FRAUD);
                        $payment->save();
                        $logData["Action"] = "Marked as FRAUD.";
                    }

                    //mark as checked
                    $transaction->setSagepaysuiteFraudCheck(1);
                    $transaction->save();

                    /**
                     * process fraud actions
                     */

                    //auto-invoice
                    $autoInvoiceActioned = $this->_processAutoInvoice(
                        $transaction,
                        $payment,
                        $response
                    );
                    if (!empty($autoInvoiceActioned)) {
                        $logData["Action"] = $autoInvoiceActioned;
                    }

                    /**
                     * END process fraud actions
                     */

                    /**
                     * save fraud information in the payment as the transaction
                     * additional info of the transactions does not seem to be working
                     */
                    $this->saveFraudInformation($response, $payment);

                    $logData = array_merge($logData, $this->getFraudInformationToLog($response));
                } else {
                    $recommendation = $this->getFraudScreenRecommendation($response);

                    //save the "not checked" or "no result" status
                    $payment->setAdditionalInformation("fraudscreenrecommendation", $recommendation);
                    $payment->save();

                    $logData["fraudscreenrecommendation"] = $recommendation;
                }
            } else {
                $responseErrorCodeShow = "INVALID";
                if ($response->getErrorCode()) {
                    $responseErrorCodeShow = $response->getErrorCode();
                }
                $logData["ERROR"] = "Invalid Response: " . $responseErrorCodeShow;
            }
        }

        return $logData;
    }

    /**
     * @param \Ebizmarts\SagePaySuite\Api\SagePayData\FraudScreenResponseInterface $fraudData
     * @return string
     */
    private function getFraudScreenRecommendation($fraudData)
    {
        $recommendation = '';

        $fraudprovidername = $fraudData->getFraudProviderName();

        if ($fraudprovidername == self::RED) {
            $recommendation = $fraudData->getFraudScreenRecommendation();
        } elseif ($fraudprovidername == self::FSG) {
            $recommendation = $fraudData->getThirdmanAction();
        }

        return $recommendation;
    }

    /**
     * @param \Ebizmarts\SagePaySuite\Api\SagePayData\FraudScreenResponseInterface $fraudData
     * @return bool
     */
    private function isPassedFraudCheck($fraudData)
    {
        $passed = false;

        $fraudprovidername = $fraudData->getFraudProviderName();

        if ($fraudprovidername == self::RED) {
            $passed = $fraudData->getFraudScreenRecommendation() == Config::REDSTATUS_ACCEPT;
        } elseif ($fraudprovidername == self::FSG) {
            $passed = $fraudData->getThirdmanAction() == Config::T3STATUS_OK;
        }

        return $passed;
    }

    /**
     * @param \Ebizmarts\SagePaySuite\Api\SagePayData\FraudScreenResponseInterface $fraudData
     * @return array
     */
    private function getFraudInformationToLog($fraudData)
    {
        $logData = [];

        $fraudprovidername = $fraudData->getFraudProviderName();

        if ($fraudprovidername == self::RED) {
            $fraudscreenrecommendation = $fraudData->getFraudScreenRecommendation();
            $fraudid                   = $fraudData->getFraudId();
            $fraudcode                 = $fraudData->getFraudCode();
            $fraudcodedetail           = $fraudData->getFraudCodeDetail();
        } elseif ($fraudprovidername == self::FSG) {
            $fraudscreenrecommendation = $fraudData->getThirdmanAction();
            $fraudid                   = $fraudData->getThirdmanId();
            $fraudcode                 = $fraudData->getThirdmanScore();
            $fraudcodedetail           = $fraudData->getThirdmanAction();
            $logData["fraudrules"]     = $fraudData->getThirdmanRulesAsArray();
        }

        $logData["fraudscreenrecommendation"] = $fraudscreenrecommendation;
        $logData["fraudid"]                   = $fraudid;
        $logData["fraudcode"]                 = $fraudcode;
        $logData["fraudcodedetail"]           = $fraudcodedetail;
        $logData["fraudprovidername"]         = $fraudprovidername;

        return $logData;
    }

    /**
     * @param \Ebizmarts\SagePaySuite\Api\SagePayData\FraudScreenResponseInterface $fraudData
     * @param $payment
     */
    private function saveFraudInformation($fraudData, $payment)
    {
        $fraudprovidername = $fraudData->getFraudProviderName();

        if ($fraudprovidername == self::RED) {
            $fraudscreenrecommendation = $fraudData->getFraudScreenRecommendation();
            $fraudid                   = $fraudData->getFraudId();
            $fraudcode                 = $fraudData->getFraudCode();
            $fraudcodedetail           = $fraudData->getFraudCodeDetail();
        } elseif ($fraudprovidername == self::FSG) {
            $fraudscreenrecommendation = $fraudData->getThirdmanAction();
            $fraudid                   = $fraudData->getThirdmanId();
            $fraudcode                 = $fraudData->getThirdmanScore();
            $fraudcodedetail           = $fraudData->getThirdmanAction();
            $payment->setAdditionalInformation("fraudrules", $fraudData->getThirdmanRulesAsArray());
        }

        $payment->setAdditionalInformation("fraudscreenrecommendation", $fraudscreenrecommendation);
        $payment->setAdditionalInformation("fraudid", $fraudid);
        $payment->setAdditionalInformation("fraudcode", $fraudcode);
        $payment->setAdditionalInformation("fraudcodedetail", $fraudcodedetail);
        $payment->setAdditionalInformation("fraudprovidername", $fraudprovidername);
        $payment->save();
    }

    /**
     * @param \Ebizmarts\SagePaySuite\Api\SagePayData\FraudScreenResponseInterface $fraudData
     * @return bool
     */
    private function transactionIsFraud($fraudData)
    {
        $isFraud = false;

        $fraudprovidername = $fraudData->getFraudProviderName();

        if ($fraudprovidername == self::RED) {
            $isFraud = $fraudData->getFraudScreenRecommendation() == Config::REDSTATUS_DENY;
        } elseif ($fraudprovidername == self::FSG) {
            $isFraud = $fraudData->getThirdmanAction() == Config::T3STATUS_REJECT;
        }

        return $isFraud;
    }

    /**
     * @param \Ebizmarts\SagePaySuite\Api\SagePayData\FraudScreenResponseInterface $fraudData
     * @return bool
     */
    private function fraudCheckAvailable($fraudData)
    {
        $providerChecked = false;

        $fraudprovidername = $fraudData->getFraudProviderName();

        if ($fraudprovidername == self::RED) {
            $providerChecked = $fraudData->getFraudScreenRecommendation() != Config::REDSTATUS_NOTCHECKED;
        } elseif ($fraudprovidername == self::FSG) {
            $providerChecked = $fraudData->getThirdmanAction() != Config::T3STATUS_NORESULT;
        }

        return $providerChecked;
    }

    /**
     * @param Transaction $transaction
     * @param Order\Payment $payment
     * @param FraudScreenResponseInterface $response
     * @return false|string
     * @throws LocalizedException
     */
    private function _processAutoInvoice(
        Transaction $transaction,
        \Magento\Sales\Model\Order\Payment $payment,
        FraudScreenResponseInterface $response
    ) {
        $providerName = $response->getFraudProviderName();
        $fraudScore = null;
        if ($providerName == Fraud::RED) {
            $fraudScore = $response->getFraudCode();
        } elseif ($providerName == Fraud::FSG) {
            $fraudScore = $response->getThirdmanScore();
        }
        $state = $payment->getOrder()->getState();
        //auto-invoice authorized order for full amount if ACCEPT or OK
        if ($this->isPassedFraudCheck($response) &&
            $this->configAutoInvoice() &&
            $this->transactionIsAuth($transaction) &&
            $this->transactionIsNotClosed($transaction) &&
            $state !== Order::STATE_HOLDED &&
            $this->configAutoInvoiceScore() > 0 &&
            $this->configAutoInvoiceScore() >= $fraudScore
        ) {
            $order = $payment->getOrder();

            if ($this->_config->getAdvancedValue('hold_order') && $this->shouldHoldOrder($payment)) {
                return false;
            }

            $invoiceService = $this->invoiceServiceFactory->create();
            $invoice = $invoiceService->prepareInvoice($order, []);

            if (!$invoice) {
                throw new LocalizedException(__('We can\'t save the invoice right now.'));
            }

            if (!$invoice->getTotalQty()) {
                throw new LocalizedException(
                    __('You can\'t create an invoice without products.')
                );
            }

            $invoice->setRequestedCaptureCase(Invoice::CAPTURE_ONLINE);

            $invoice->register();

            $invoice->getOrder()->setCustomerNoteNotify(false);
            $invoice->getOrder()->setIsInProcess(true);

            $transactionSave = $this->transactionFactory->create();
            $transactionSave->addObject($invoice);
            $transactionSave->addObject($invoice->getOrder());
            $transactionSave->save();

            return "Captured online, invoice #{$invoice->getId()} generated.";
        } else {
            return false;
        }
    }

    /**
     * @param $order
     * @return bool
     * @throws LocalizedException
     */
    private function shouldHoldOrder($payment)
    {
        $order = $payment->getOrder();
        $billingAddressStreet = $order->getBillingAddress()->getStreet();
        $shippingAddressStreet = $order->getShippingAddress()->getStreet();

        if ($billingAddressStreet === $shippingAddressStreet) {
            return false;
        }

        if (!$order->canHold()) {
            return false;
        }

        $order->hold();
        $order->addStatusHistoryComment(__('Order placed on hold by high fraud risk setting.'));
        $this->orderRepository->save($order);

        return true;
    }

    /**
     * @param Transaction $transaction
     * @return bool
     */
    protected function transactionIsAuth(Transaction $transaction): bool
    {
        return $transaction->getTxnType() == Transaction::TYPE_AUTH;
    }

    /**
     * @param Transaction $transaction
     * @return bool
     */
    protected function transactionIsNotClosed(Transaction $transaction): bool
    {
        return (bool)$transaction->getIsTransactionClosed() == false;
    }

    /**
     * @return bool
     */
    protected function configAutoInvoice(): bool
    {
        return (bool)$this->_config->getAutoInvoiceFraudPassed() == true;
    }

    /**
     * @return float
     */
    protected function configAutoInvoiceScore(): float
    {
        return $this->_config->getAutoInvoiceScore();
    }

    public function processAutoInvoiceForTests(
        Transaction $transaction,
        \Magento\Sales\Model\Order\Payment $payment,
        $response
    ) {
        return $this->_processAutoInvoice(
            $transaction,
            $payment,
            $response
        );
    }
}
