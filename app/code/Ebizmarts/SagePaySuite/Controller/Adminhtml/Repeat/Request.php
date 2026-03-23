<?php
/**
 * Copyright © 2018 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Controller\Adminhtml\Repeat;

use Ebizmarts\SagePaySuite\Helper\Checkout as SuiteHelperCheckout;
use Ebizmarts\SagePaySuite\Helper\Data as SuiteHelper;
use Ebizmarts\SagePaySuite\Helper\Request as SuiteHelperRequest;
use Ebizmarts\SagePaySuite\Model\Api\ApiException;
use Ebizmarts\SagePaySuite\Model\Api\Shared as SuiteSharedApi;
use Ebizmarts\SagePaySuite\Model\Config;
use Ebizmarts\SagePaySuite\Model\Config\ClosedForActionFactory;
use Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger;
use Ebizmarts\SagePaySuite\Model\RecoverCart;
use Magento\Backend\Model\Session\Quote;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Quote\Model\QuoteManagement;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Payment\TransactionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\GiftMessage\Model\Save;

class Request implements ActionInterface
{
    /**
     * @var \Ebizmarts\SagePaySuite\Model\Config
     */
    private $config;

    /**
     * @var SuiteHelper
     */
    private $suiteHelper;

    /**
     * @var \Magento\Quote\Model\Quote
     */
    private $quote;

    /**
     * Logging instance
     * @var \Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger
     */
    private $suiteLogger;

    /**
     * @var SuiteHelperCheckout
     */
    private $checkoutHelper;

    /**
     *  POST array
     */
    private $postData;

    /**
     * @var QuoteManagement
     */
    private $quoteManagement;

    /**
     * Sage Pay Suite Request Helper
     * @var SuiteHelperRequest
     */
    private $requestHelper;

    /**
     * @var SuiteSharedApi
     */
    private $sharedApi;

    /**
     * @var TransactionFactory
     */
    private $transactionFactory;

    /** @var ClosedForActionFactory */
    private $actionFactory;
    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @var UrlInterface
     */
    private $backendUrl;
    /**
     * @var ManagerInterface
     */
    private $messageManager;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /** @var RecoverCart */
    private $recoverCart;

    /** @var Save */
    private $giftMessageSave;

    /**
     * @param Config $config
     * @param SuiteHelper $suiteHelper
     * @param Logger $suiteLogger
     * @param Quote $quoteSession
     * @param SuiteHelperCheckout $checkoutHelper
     * @param QuoteManagement $quoteManagement
     * @param SuiteHelperRequest $requestHelper
     * @param SuiteSharedApi $sharedApi
     * @param TransactionFactory $transactionFactory
     * @param ClosedForActionFactory $actionFactory
     * @param RequestInterface $request
     * @param UrlInterface $backendUrl
     * @param ManagerInterface $manager
     * @param ResultFactory $resultFactory
     * @param RecoverCart $recoverCart
     * @param Save $giftMessageSave
     */
    public function __construct(
        Config $config,
        SuiteHelper $suiteHelper,
        Logger $suiteLogger,
        Quote $quoteSession,
        SuiteHelperCheckout $checkoutHelper,
        QuoteManagement $quoteManagement,
        SuiteHelperRequest $requestHelper,
        SuiteSharedApi $sharedApi,
        TransactionFactory $transactionFactory,
        ClosedForActionFactory $actionFactory,
        RequestInterface $request,
        UrlInterface $backendUrl,
        ManagerInterface $manager,
        StoreManagerInterface $storeManager,
        ResultFactory $resultFactory,
        RecoverCart $recoverCart,
        Save $giftMessageSave
    ) {

        $this->config = $config;
        $this->config->setMethodCode(\Ebizmarts\SagePaySuite\Model\Config::METHOD_REPEAT);
        $this->suiteHelper        = $suiteHelper;
        $this->suiteLogger        = $suiteLogger;
        $this->sharedApi          = $sharedApi;
        $this->checkoutHelper     = $checkoutHelper;
        $this->quoteManagement    = $quoteManagement;
        $this->requestHelper      = $requestHelper;
        $this->transactionFactory = $transactionFactory;
        $this->actionFactory      = $actionFactory;
        $this->quote              = $quoteSession->getQuote();
        $this->request            = $request;
        $this->backendUrl         = $backendUrl;
        $this->messageManager     = $manager;
        $this->storeManager       = $storeManager;
        $this->resultFactory      = $resultFactory;
        $this->recoverCart        = $recoverCart;
        $this->giftMessageSave    = $giftMessageSave;
    }

    public function execute()
    {
        $order = null;
        try {
            //parse POST data
            $this->postData = $this->getRequest()->getPost();

            $giftmessages = $this->getRequest()->getPost('giftmessage');
            if ($giftmessages) {
                $this->giftMessageSave->setGiftmessages($giftmessages)->saveAllInQuote();
            }

            //prepare quote
            $this->quote->collectTotals();
            $this->quote->reserveOrderId();
            $vendorTxCode = $this->suiteHelper->generateVendorTxCode($this->quote->getReservedOrderId());

            //generate request data
            $request = $this->_generateRequest($vendorTxCode);

            //save order
            $order = $this->quoteManagement->submit($this->quote);

            //send REPEAT POST to Sage Pay
            $post_response = $this->sharedApi->repeatTransaction(
                $this->postData->vpstxid,
                $request,
                $order,
                $this->config->getSagepayPaymentAction()
            );

            //set payment info for save order

            //strip brackets
            $transactionId = $this->suiteHelper->removeCurlyBraces($post_response["data"]["VPSTxId"]);

            $payment = $this->quote->getPayment();
            $payment->setMethod(Config::METHOD_REPEAT);
            $payment->setTransactionId($transactionId);
            $payment->setAdditionalInformation('statusDetail', $post_response["data"]["StatusDetail"]);
            $payment->setAdditionalInformation('vendorTxCode', $vendorTxCode);
            $payment->setAdditionalInformation('paymentAction', $this->config->getSagepayPaymentAction());
            $payment->setAdditionalInformation('moto', true);
            $payment->setAdditionalInformation('vendorname', $this->config->getVendorname());
            $payment->setAdditionalInformation('mode', $this->config->getMode());

            if ($order) {
                //mark order as paid
                $this->_confirmPayment($transactionId, $order, $post_response["data"]["StatusDetail"], $vendorTxCode);

                //add success url to response
                $params = [
                    '_nosid' => true,
                    '_secure' => true,
                    '_store' => $this->storeManager->getStore(),
                    'order_id' => $order->getId()
                ];
                $url = $this->backendUrl->getUrl('sales/order/view', $params);
                $post_response["data"]["redirect"] = $url;

                //prepare response
                $responseContent = [
                    'success' => true,
                    'response' => $post_response
                ];

                $this->messageManager->addSuccessMessage(
                    __("You created the order, Opayo transaction succesfully authorised.")
                );
            } else {
                throw new \Magento\Framework\Validator\Exception(__('Unable to save Opayo order.'));
            }
        } catch (ApiException $apiException) {
            $this->tryCancelOrder($order);
            $this->suiteLogger->logException($apiException, [__METHOD__, __LINE__]);
            $responseContent = [
                'success' => false,
                'error_message' => __('Something went wrong: %1', $apiException->getUserMessage()),
            ];
        } catch (\Exception $e) {
            $this->tryCancelOrder($order);
            $this->suiteLogger->logException($e, [__METHOD__, __LINE__]);
            $responseContent = [
                'success' => false,
                'error_message' => __('Something went wrong: %1', $e->getMessage()),
            ];
        }

        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($responseContent);
        return $resultJson;
    }

    private function _generateRequest($vendorTxCode)
    {
        $data = [];

        $data['VendorTxCode'] = $vendorTxCode;
        $data['Description']  = $this->requestHelper->getOrderDescription(true);
        $data['ReferrerID']   = $this->requestHelper->getReferrerId();

        //populate payment amount information
        $amount = $this->requestHelper->populatePaymentAmountAndCurrency($this->quote);
        $data = array_merge($data, $amount);

        //populate address information
        $data = array_merge($data, $this->requestHelper->populateAddressInformation($this->quote));

        return $data;
    }

    /**
     * @param string $transactionId
     * @param OrderInterface $order
     * @param string $statusDetail
     * @param string $vendorTxCode
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function _confirmPayment($transactionId, $order, $statusDetail, $vendorTxCode)
    {
        /** @var OrderPaymentInterface $payment */
        $payment = $order->getPayment();
        $payment->setTransactionId($transactionId);
        $payment->setLastTransId($transactionId);
        $payment->setAdditionalInformation('statusDetail', $statusDetail);
        $payment->setAdditionalInformation('vendorTxCode', $vendorTxCode);
        $payment->setAdditionalInformation('paymentAction', $this->config->getSagepayPaymentAction());
        $payment->setAdditionalInformation('moto', true);
        $payment->setAdditionalInformation('vendorname', $this->config->getVendorname());
        $payment->setAdditionalInformation('mode', $this->config->getMode());

        $sagePayPaymentAction = $this->config->getSagepayPaymentAction();

        //leave transaction open in case defer
        if ($sagePayPaymentAction === Config::ACTION_REPEAT_DEFERRED) {
            /** @var \Ebizmarts\SagePaySuite\Model\Config\ClosedForAction $actionClosed */
            $actionClosed = $this->actionFactory->create(['paymentAction' => $sagePayPaymentAction]);
            list($action, $closed) = $actionClosed->getActionClosedForPaymentAction();

            /** @var \Magento\Sales\Model\Order\Payment\Transaction $transaction */
            $transaction = $this->transactionFactory->create();
            $transaction->setOrderPaymentObject($payment);
            $transaction->setTxnId($transactionId);
            $transaction->setOrderId($order->getEntityId());
            $transaction->setTxnType($action);
            $transaction->setPaymentId($payment->getId());
            $transaction->setIsClosed($closed);
            $transaction->save();
        }

        $payment->save();

        if ($sagePayPaymentAction === Config::ACTION_REPEAT) {
            $payment->getMethodInstance()->markAsInitialized();
        }

        $order->place()->save();

        //send email
        $this->checkoutHelper->sendOrderEmail($order);
    }

    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param int $orderId
     * @return void
     */
    private function tryCancelOrder($order)
    {
        if ($order !== null) {
            $this->recoverCart->setShouldCancelOrders(true)->execute($order->getId());
        }
    }
}
