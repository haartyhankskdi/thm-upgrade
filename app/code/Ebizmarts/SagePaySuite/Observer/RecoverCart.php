<?php

namespace Ebizmarts\SagePaySuite\Observer;

use Ebizmarts\SagePaySuite\Model\SessionInterface as SagePaySession;
use Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;

class RecoverCart implements ObserverInterface
{
    /** @var Session */
    private $session;

    /** @var Logger */
    private $suiteLogger;

    /** @var ManagerInterface */
    private $messageManager;

    /** @var UrlInterface */
    private $urlInterface;

    /** @var Http  */
    private $request;
    /**
     * @var RedirectInterface
     */
    private $redirect;
    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * @param Session $session
     * @param Logger $suiteLogger
     * @param ManagerInterface $messageManager
     * @param UrlInterface $urlInterface
     * @param RedirectInterface $redirect
     * @param ResponseInterface $response
     * @param Http $request
     */
    public function __construct(
        Session $session,
        Logger $suiteLogger,
        ManagerInterface $messageManager,
        UrlInterface $urlInterface,
        RedirectInterface $redirect,
        ResponseInterface $response,
        Http $request
    ) {
        $this->session = $session;
        $this->suiteLogger = $suiteLogger;
        $this->messageManager = $messageManager;
        $this->urlInterface = $urlInterface;
        $this->request = $request;
        $this->redirect = $redirect;
        $this->response = $response;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if ($this->filterActions()) {
            $presavedOrderId = $this->session->getData(SagePaySession::PRESAVED_PENDING_ORDER_KEY);
            $convertingQuoteToOrder = $this->session->getData(SagePaySession::CONVERTING_QUOTE_TO_ORDER);
            if ($this->checkIfRecoverCartIsPossible($presavedOrderId, $convertingQuoteToOrder)) {
                $this->session->setData(SagePaySession::CONVERTING_QUOTE_TO_ORDER, 0);
                $this->redirect->redirect($this->response, "elavon/cart/recover");
            }
        }
    }

    /**
     * @param $presavedOrderId
     * @param $convertingQuoteToOrder
     * @return bool
     */
    private function checkIfRecoverCartIsPossible($presavedOrderId, $convertingQuoteToOrder)
    {
        return $this->checkPreSavedOrder($presavedOrderId) &&
        $this->checkQuoteIsConvertingToOrder($convertingQuoteToOrder);
    }

    /**
     * @param $presavedOrderId
     * @return bool
     */
    private function checkPreSavedOrder($presavedOrderId)
    {
        return !empty($presavedOrderId);
    }

    /**
     * @param $convertingQuoteToOrder
     * @return bool
     */
    private function checkQuoteIsConvertingToOrder($convertingQuoteToOrder)
    {
        return $convertingQuoteToOrder === 1;
    }

    /**
     * @return bool
     */
    public function filterActions()
    {
        return $this->request->getFrontName() !== 'rest' &&
            $this->request->getFrontName() !== 'graphql' &&
            $this->request->getFrontName() !== 'elavon' &&
            strtolower((string)$this->request->getFrontName()) !== 'hyvaelavon' &&
            $this->request->getFullActionName() !== 'customer_section_load';
    }
}
