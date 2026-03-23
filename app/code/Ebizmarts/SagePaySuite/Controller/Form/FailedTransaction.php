<?php

namespace Ebizmarts\SagePaySuite\Controller\Form;

use Ebizmarts\SagePaySuite\Model\ObjectLoader\OrderLoader;
use Ebizmarts\SagePaySuite\Model\RecoverCart;
use Ebizmarts\SagePaySuite\Model\SessionInterface as SagePaySession;
use Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\QuoteRepository;
use Magento\Sales\Api\Data\OrderPaymentInterface;

class FailedTransaction implements ActionInterface
{
    /** @var OrderLoader */
    private $orderLoader;

    /** @var EncryptorInterface */
    private $encryptor;

    /** @var QuoteRepository */
    private $quoteRepository;

    /** @var Logger */
    private $suiteLogger;

    /** @var RecoverCart */
    private $recoverCart;

    /** @var Session */
    private $checkoutSession;

    /** @var RequestInterface */
    private $request;

    /** @var ManagerInterface */
    private $messageManager;

    /** @var RedirectInterface */
    private $redirect;

    /** @var ResponseInterface */
    private $response;

    /**
     * @param Logger $suiteLogger
     * @param QuoteRepository $quoteRepository
     * @param EncryptorInterface $encryptor
     * @param OrderLoader $orderLoader
     * @param RecoverCart $recoverCart
     * @param Session $checkoutSession*
     * @param ManagerInterface $manager
     * @param RequestInterface $request
     * @param RedirectInterface $redirect
     * @param ResponseInterface $response
     */
    public function __construct(
        Logger $suiteLogger,
        QuoteRepository $quoteRepository,
        EncryptorInterface $encryptor,
        OrderLoader $orderLoader,
        RecoverCart $recoverCart,
        Session $checkoutSession,
        ManagerInterface $manager,
        RequestInterface $request,
        RedirectInterface $redirect,
        ResponseInterface $response
    ) {
        $this->suiteLogger = $suiteLogger;
        $this->quoteRepository = $quoteRepository;
        $this->encryptor = $encryptor;
        $this->orderLoader = $orderLoader;
        $this->recoverCart = $recoverCart;
        $this->checkoutSession = $checkoutSession;
        $this->messageManager = $manager;
        $this->request = $request;
        $this->redirect = $redirect;
        $this->response = $response;
    }

    /**
     * @return ResponseInterface
     */
    public function execute()
    {
        $quoteIdFromParams = null;
        try {
            $request = $this->getRequest();
            $quoteIdEncrypted = $request->getParam("quoteId");
            $errorMessage = $request->getParam("errorMessage");
            $quoteIdFromParams = $this->encryptor->decrypt($quoteIdEncrypted);
            /** @var CartInterface $quote */
            $quote = $this->quoteRepository->get((int)$quoteIdFromParams);
            $this->suiteLogger->debugLog($quote->getData(), [__METHOD__, __LINE__]);
            $order = $this->orderLoader->loadOrderFromQuote($quote);
            $this->suiteLogger->debugLog($order->getData(), [__METHOD__, __LINE__]);
            /** @var OrderPaymentInterface $payment */
            $payment = $order->getPayment();
            $payment->setAdditionalInformation('statusDetail', $errorMessage);
            $payment->save();
            $this->recoverCart->setShouldCancelOrders(true)->execute();
            $this->checkoutSession->setData(SagePaySession::PRESAVED_PENDING_ORDER_KEY, null);
            $this->messageManager->addErrorMessage($errorMessage);
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
            $this->suiteLogger->sageLog(Logger::LOG_EXCEPTION, $exception->getMessage() .
            " - quote ID: " . $quoteIdFromParams, [__METHOD__, __LINE__]);
            $this->suiteLogger->sageLog(Logger::LOG_EXCEPTION, $exception->getTraceAsString(), [__METHOD__, __LINE__]);
        }

        $this->redirect->redirect($this->response, 'checkout/cart');

        return $this->response;
    }

    protected function getRequest()
    {
        return $this->request;
    }
}
