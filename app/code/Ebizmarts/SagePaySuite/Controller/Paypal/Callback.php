<?php
/**
 * Copyright © 2015 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Controller\Paypal;

use Ebizmarts\SagePaySuite\Helper\Data as SuiteHelper;
use Ebizmarts\SagePaySuite\Model\Api\Post;
use Ebizmarts\SagePaySuite\Model\Config;
use Ebizmarts\SagePaySuite\Model\ObjectLoader\OrderLoader;
use Ebizmarts\SagePaySuite\Model\OrderUpdateOnCallback;
use Ebizmarts\SagePaySuite\Model\RecoverCart;
use Ebizmarts\SagePaySuite\Model\SessionInterface as SagePaySession;
use Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Validator\Exception as ValidatorException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteRepository;

class Callback implements ActionInterface, CsrfAwareActionInterface
{
    /** @var Config */
    private $config;

    /** @var Quote */
    private $quote;

    /** @var Session */
    private $checkoutSession;

    /** @var Logger */
    private $suiteLogger;

    private $postData;

    /** @var Post */
    private $postApi;

    /** @var OrderUpdateOnCallback */
    private $updateOrderCallback;

    /** @var SuiteHelper */
    private $suiteHelper;

    /** @var EncryptorInterface */
    private $encryptor;

    /** @var RecoverCart */
    private $recoverCart;

    /** @var OrderLoader */
    private $orderLoader;

    /** @var QuoteRepository */
    private $quoteRepository;

    /** @var RequestInterface */
    private $request;

    /** @var ManagerInterface */
    private $messageManager;

    /** @var RedirectInterface */
    private $redirect;

    /** @var ResponseInterface */
    private $response;

    /**
     * @param Session $checkoutSession
     * @param Config $config
     * @param Logger $suiteLogger
     * @param Post $postApi
     * @param Quote $quote
     * @param QuoteRepository $quoteRepository
     * @param OrderUpdateOnCallback $updateOrderCallback
     * @param SuiteHelper $suiteHelper
     * @param EncryptorInterface $encryptor
     * @param RecoverCart $recoverCart
     * @param OrderLoader $orderLoader
     * @param ManagerInterface $manager
     * @param RequestInterface $request
     * @param RedirectInterface $redirect
     * @param ResponseInterface $response
     */
    public function __construct(
        Session $checkoutSession,
        Config $config,
        Logger $suiteLogger,
        Post $postApi,
        Quote $quote,
        QuoteRepository $quoteRepository,
        OrderUpdateOnCallback $updateOrderCallback,
        SuiteHelper $suiteHelper,
        EncryptorInterface $encryptor,
        RecoverCart $recoverCart,
        OrderLoader $orderLoader,
        ManagerInterface $manager,
        RequestInterface $request,
        RedirectInterface $redirect,
        ResponseInterface $response
    ) {
        $this->config               = $config;
        $this->checkoutSession      = $checkoutSession;
        $this->suiteLogger          = $suiteLogger;
        $this->postApi              = $postApi;
        $this->quote                = $quote;
        $this->quoteRepository      = $quoteRepository;
        $this->updateOrderCallback  = $updateOrderCallback;
        $this->suiteHelper          = $suiteHelper;
        $this->encryptor            = $encryptor;
        $this->recoverCart          = $recoverCart;
        $this->orderLoader          = $orderLoader;
        $this->messageManager      = $manager;
        $this->request             = $request;
        $this->redirect            = $redirect;
        $this->response            = $response;

        $this->config->setMethodCode(Config::METHOD_PAYPAL);
    }

    /**
     * @return ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $orderId = null;
        try {
            $this->loadQuoteFromDataSource();
            $order = $this->orderLoader->loadOrderFromQuote($this->quote);
            $orderId = $order->getId();

            //get POST data
            $this->postData = $this->getRequest()->getPost();

            //log response
            $this->suiteLogger->sageLog(Logger::LOG_REQUEST, $this->postData, [__METHOD__, __LINE__]);

            $this->validatePostDataStatusAndStatusDetail();

            $completionResponse = $this->sendCompletionPost()["data"];

            $transactionId = $completionResponse["VPSTxId"];
            $transactionId = $this->suiteHelper->removeCurlyBraces($transactionId);

            $payment = $order->getPayment();

            $this->updatePaymentInformation($transactionId, $payment, $completionResponse);

            $this->updateOrderCallback->setOrder($order);
            $this->updateOrderCallback->confirmPayment($transactionId);

            //prepare session to success or cancellation page
            $this->checkoutSession->clearHelperData();
            $this->checkoutSession->setLastQuoteId($this->quote->getId());
            $this->checkoutSession->setLastSuccessQuoteId($this->quote->getId());
            $this->checkoutSession->setLastOrderId($order->getId());
            $this->checkoutSession->setLastRealOrderId($order->getIncrementId());
            $this->checkoutSession->setLastOrderStatus($order->getStatus());
            $this->checkoutSession->setData(SagePaySession::PRESAVED_PENDING_ORDER_KEY, null);

            $this->redirect->redirect($this->response, 'checkout/onepage/success');
        } catch (\Exception $e) {
            $this->recoverCart->setShouldCancelOrders(true)->execute($orderId);
            $this->suiteLogger->logException($e);
            $this->checkoutSession->setData(SagePaySession::PRESAVED_PENDING_ORDER_KEY, null);
            $this->checkoutSession->setData(SagePaySession::CONVERTING_QUOTE_TO_ORDER, 0);
            $this->redirectToCartAndShowError('We can\'t place the order: ' . $e->getMessage());
        }
        return $this->response;
    }

    private function sendCompletionPost()
    {
        $request = [
            "VPSProtocol" => $this->config->getVPSProtocol(),
            "TxType"      => "COMPLETE",
            "VPSTxId"     => $this->postData->VPSTxId,
            "Amount"      => $this->getAuthorisedAmount(),
            "Accept"      => "YES"
        ];

        return $this->postApi->sendPost(
            $request,
            $this->getServiceURL(),
            ["OK", 'REGISTERED', 'AUTHENTICATED'],
            'Invalid response from PayPal'
        );
    }

    private function getAuthorisedAmount()
    {
        $quoteAmount = $this->config->getQuoteAmount($this->quote);
        $amount = number_format($quoteAmount, 2, '.', '');
        return $amount;
    }

    /**
     * Redirect customer to shopping cart and show error message
     *
     * @param string $errorMessage
     * @return void
     */
    private function redirectToCartAndShowError($errorMessage)
    {
        $this->messageManager->addErrorMessage($errorMessage);
        $this->redirect->redirect($this->response, 'checkout/cart');
    }

    private function getServiceURL()
    {
        if ($this->config->getMode() == Config::MODE_LIVE) {
            return Config::URL_PAYPAL_COMPLETION_LIVE;
        } else {
            return Config::URL_PAYPAL_COMPLETION_TEST;
        }
    }

    private function validatePostDataStatusAndStatusDetail()
    {
        if (empty($this->postData) || !isset($this->postData->Status) || $this->postData->Status != "PAYPALOK") {
            if (!empty($this->postData) && isset($this->postData->StatusDetail)) {
                throw new LocalizedException(__("Can not place PayPal orders: %1", $this->postData->StatusDetail));
            } else {
                throw new LocalizedException(__("Can not place PayPal order, please try another payment method"));
            }
        }
    }

    private function loadQuoteFromDataSource()
    {
        $quoteId = $this->encryptor->decrypt($this->getRequest()->getParam("quoteid"));
        $this->quote = $this->quoteRepository->get($quoteId);

        if (!isset($this->quote) || empty($this->quote->getId())) {
            throw new LocalizedException(__("Unable to find payment data."));
        }
    }

    /**
     * @param $transactionId
     * @param $payment
     * @param $completionResponse
     * @throws ValidatorException
     */
    private function updatePaymentInformation($transactionId, $payment, $completionResponse)
    {
        $this->suiteLogger->sageLog(
            Logger::LOG_REQUEST,
            "Flag TransactionId: " . $transactionId,
            [__METHOD__, __LINE__]
        );
        $this->suiteLogger->sageLog(
            Logger::LOG_REQUEST,
            "Flag getLastTransId: " . $payment->getLastTransId(),
            [__METHOD__, __LINE__]
        );

        if (!empty($transactionId) && $payment->getLastTransId() == $transactionId) {
            $payment->setAdditionalInformation('statusDetail', $completionResponse['StatusDetail']);
            $payment->setAdditionalInformation('AVSCV2', $completionResponse['AVSCV2']);
            $payment->setAdditionalInformation('AddressResult', $completionResponse['AddressResult']);
            $payment->setAdditionalInformation('PostCodeResult', $completionResponse['PostCodeResult']);
            $payment->setAdditionalInformation('CV2Result', $completionResponse['CV2Result']);
            $payment->setAdditionalInformation('3DSecureStatus', $completionResponse['3DSecureStatus']);
            $payment->setCcType("PayPal");
            $payment->setLastTransId($transactionId);
            $payment->save();
        } else {
            throw new ValidatorException(__('Invalid transaction id'));
        }
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
     * @return RequestInterface
     */
    protected function getRequest()
    {
        return $this->request;
    }
}
