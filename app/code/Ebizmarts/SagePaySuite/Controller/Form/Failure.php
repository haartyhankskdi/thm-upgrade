<?php
declare(strict_types=1);
/**
 * Copyright © 2015 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Controller\Form;

use Ebizmarts\SagePaySuite\Model\SessionInterface as SagePaySession;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;

use Ebizmarts\SagePaySuite\Model\Form;
use Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger;
use Ebizmarts\SagePaySuite\Model\RecoverCart;
use Magento\Checkout\Model\Session;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Model\OrderFactory;
use Psr\Log\LoggerInterface;

class Failure implements ActionInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Logging instance
     * @var \Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger
     */
    private $suiteLogger;

    /**
     * @var Form
     */
    private $formModel;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /** @var RecoverCart */
    private $recoverCart;
    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @var ManagerInterface
     */
    private $messageManager;
    /**
     * @var RedirectInterface
     */
    private $redirect;
    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * @param Logger $suiteLogger
     * @param LoggerInterface $logger
     * @param Form $formModel
     * @param OrderFactory $orderFactory
     * @param QuoteFactory $quoteFactory
     * @param Session $checkoutSession
     * @param EncryptorInterface $encryptor
     * @param RecoverCart $recoverCart
     * @param ManagerInterface $manager
     * @param RequestInterface $request
     * @param RedirectInterface $redirect
     * @param ResponseInterface $response
     */
    public function __construct(
        Logger $suiteLogger,
        LoggerInterface $logger,
        Form $formModel,
        OrderFactory $orderFactory,
        QuoteFactory $quoteFactory,
        Session $checkoutSession,
        EncryptorInterface $encryptor,
        RecoverCart $recoverCart,
        ManagerInterface $manager,
        RequestInterface $request,
        RedirectInterface $redirect,
        ResponseInterface $response
    ) {
        $this->suiteLogger     = $suiteLogger;
        $this->logger          = $logger;
        $this->formModel       = $formModel;
        $this->orderFactory    = $orderFactory;
        $this->quoteFactory    = $quoteFactory;
        $this->checkoutSession = $checkoutSession;
        $this->encryptor       = $encryptor;
        $this->recoverCart     = $recoverCart;
        $this->messageManager  = $manager;
        $this->request         = $request;
        $this->redirect        = $redirect;
        $this->response        = $response;
    }

    /**
     * @return ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            //decode response
            $response = $this->formModel->decodeSagePayResponse($this->getRequest()->getParam("crypt"));

            //log response
            $this->suiteLogger->sageLog(Logger::LOG_REQUEST, $response, [__METHOD__, __LINE__]);

            if (!isset($response["Status"]) || !isset($response["StatusDetail"])) {
                throw new LocalizedException(__('Invalid response from Opayo'));
            }

            $orderId = $this->encryptor->decrypt($this->getRequest()->getParam("orderId"));
            $this->suiteLogger->debugLog('OrderId: ' . $orderId, [__METHOD__, __LINE__]);
            $this->recoverCart
                ->setShouldCancelOrders(true)
                ->execute();

            $statusDetail = $this->extractStatusDetail($response);

            $this->checkoutSession->setData(SagePaySession::PRESAVED_PENDING_ORDER_KEY, null);

            $this->messageManager->addErrorMessage($response["Status"] . ": " . $statusDetail);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->logger->critical($e);
        }

        $this->addOrderEndLog($response);

        $this->redirect->redirect($this->response, 'checkout/cart');
        return $this->response;
    }

    /**
     * @param array $response
     * @return string
     */
    private function extractStatusDetail(array $response): string
    {
        $statusDetail = $response["StatusDetail"];

        if (strpos($statusDetail, ':') !== false) {
            $statusDetail = explode(" : ", $statusDetail);
            $statusDetail = $statusDetail[1];
        }

        return $statusDetail;
    }

    /**
     * @param array $response
     * @return string
     */
    private function extractIncrementIdFromVendorTxCode(array $response): string
    {
        $vendorTxCode = explode("-", $response['VendorTxCode']);
        return $vendorTxCode[0];
    }

    /**
     * @param array $response
     */
    private function addOrderEndLog(array $response): void
    {
        $quoteId = $this->encryptor->decrypt($this->getRequest()->getParam("quoteid"));
        $orderId = isset($response['VendorTxCode']) ? $this->extractIncrementIdFromVendorTxCode($response) : "";
        $vpstxid = isset($response['VPSTxId']) ? $response['VPSTxId'] : "";
        $this->suiteLogger->orderEndLog($orderId, $quoteId, $vpstxid);
    }
    protected function getRequest()
    {
        return $this->request;
    }
}
