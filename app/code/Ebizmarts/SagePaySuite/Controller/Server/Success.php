<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Controller\Server;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Response\RedirectInterface;


use Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger;
use Ebizmarts\SagePaySuite\Model\ObjectLoader\OrderLoader;
use Magento\Checkout\Model\Session;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Quote\Model\QuoteRepository;
use Psr\Log\LoggerInterface;

class Success implements ActionInterface
{

    /**
     * Logging instance
     * @var \Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger
     */
    private $suiteLogger;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var OrderLoader
     */
    private $orderLoader;

    /**
     * @var RedirectFactory
     */
    protected $resultRedirectFactory;
    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @var ManagerInterface
     */
    private $messageManager;
    /**
     * @var ResponseInterface
     */
    private $response;
    /**
     * @var RedirectInterface
     */
    private $redirect;

    /**
     * @param Logger $suiteLogger
     * @param LoggerInterface $logger
     * @param Session $checkoutSession
     * @param QuoteRepository $quoteRepository
     * @param EncryptorInterface $encryptor
     * @param OrderLoader $orderLoader
     * @param RedirectFactory $resultRedirectFactory
     * @param RequestInterface $request
     * @param ManagerInterface $manager
     * @param ResponseInterface $response
     * @param RedirectInterface $redirect
     */
    public function __construct(
        Logger $suiteLogger,
        LoggerInterface $logger,
        Session $checkoutSession,
        QuoteRepository $quoteRepository,
        EncryptorInterface $encryptor,
        OrderLoader $orderLoader,
        RedirectFactory $resultRedirectFactory,
        RequestInterface $request,
        ManagerInterface $manager,
        ResponseInterface $response,
        RedirectInterface $redirect
    ) {

        $this->suiteLogger     = $suiteLogger;
        $this->logger          = $logger;
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
        $this->encryptor        = $encryptor;
        $this->orderLoader      = $orderLoader;
        $this->resultRedirectFactory   =  $resultRedirectFactory;
        $this->request          = $request;
        $this->messageManager   = $manager;
        $this->response         = $response;
        $this->redirect         = $redirect;
    }

    /**
     * @return ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $request = $this->getRequest();
            $this->suiteLogger->debugLog($request->getParams(), [__METHOD__, __LINE__]);

            $storeId = $request->getParam("_store");
            $quoteId = $this->encryptor->decrypt($request->getParam("quoteid"));

            $quote = $this->quoteRepository->get($quoteId, [$storeId]);

            $order = $this->orderLoader->loadOrderFromQuote($quote);

            //prepare session to success page
            $this->checkoutSession->clearHelperData();
            $this->checkoutSession->setLastQuoteId($quote->getId());
            $this->checkoutSession->setLastSuccessQuoteId($quote->getId());
            $this->checkoutSession->setLastOrderId($order->getEntityId());
            $this->checkoutSession->setLastRealOrderId($order->getIncrementId());
            $this->checkoutSession->setLastOrderStatus($order->getStatus());

            //remove order pre-saved flag from checkout
            $this->checkoutSession->setData(
                \Ebizmarts\SagePaySuite\Model\SessionInterface::PRESAVED_PENDING_ORDER_KEY,
                null
            );
            $this->checkoutSession->setData(
                \Ebizmarts\SagePaySuite\Model\SessionInterface::CONVERTING_QUOTE_TO_ORDER,
                0
            );
            $this->suiteLogger->orderEndLog($order->getIncrementId(), $quoteId, $order->getPayment()->getLastTransId());
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage(__('An error ocurred.'));
        }

        $this->redirect->redirect($this->response, 'checkout/onepage/success', ['_secure' => true]);

        return $this->response;
    }
    protected function getRequest()
    {
        return $this->request;
    }
}
