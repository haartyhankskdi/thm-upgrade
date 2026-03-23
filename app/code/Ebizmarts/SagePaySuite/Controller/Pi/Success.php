<?php

namespace Ebizmarts\SagePaySuite\Controller\Pi;

use Ebizmarts\SagePaySuite\Model\Config;
use Ebizmarts\SagePaySuite\Model\CryptAndCodeData;
use Ebizmarts\SagePaySuite\Model\ObjectLoader\OrderLoader;
use Magento\Checkout\Model\Type\Onepage;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;

class Success implements ActionInterface, CsrfAwareActionInterface
{
    /** @var Config */
    private $config;

    /** @var Onepage */
    private $onepage;

    /** @var OrderLoader */
    private $orderLoader;

    /** @var RequestInterface */
    private $request;

    /** @var ResponseInterface */
    private $response;

    /** @var RedirectInterface */
    private $redirect;

    /** @var CryptAndCodeData */
    private $cryptAndCode;

    /**
     * @param Onepage $onepage
     * @param Config $config
     * @param OrderLoader $orderLoader
     * @param CryptAndCodeData $cryptAndCode
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param RedirectInterface $redirect
     */
    public function __construct(
        Onepage $onepage,
        Config $config,
        OrderLoader $orderLoader,
        CryptAndCodeData $cryptAndCode,
        RequestInterface $request,
        ResponseInterface $response,
        RedirectInterface $redirect
    ) {
        $this->config = $config;
        $this->onepage = $onepage;
        $this->orderLoader = $orderLoader;
        $this->cryptAndCode = $cryptAndCode;
        $this->config->setMethodCode(Config::METHOD_PI);
        $this->request = $request;
        $this->response = $response;
        $this->redirect = $redirect;
    }

    /**
     * @return ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $session = $this->onepage->getCheckout();
        $encryptedQuoteId = $this->getRequest()->getParam("quoteId");
        $quoteId = $this->cryptAndCode->decodeAndDecrypt($encryptedQuoteId);
        $encryptedOrderId = $this->getRequest()->getParam("orderId");
        $orderId = $this->cryptAndCode->decodeAndDecrypt($encryptedOrderId);
        if ($quoteId) {
            $session->setLastSuccessQuoteId($quoteId);
            $session->setLastQuoteId($quoteId);
        }

        if ($orderId) {
            $session->setLastOrderId($orderId);
            $order = $this->orderLoader->getById($orderId);
            $session->setLastRealOrderId($order->getIncrementId());
        }
        $session->setData(\Ebizmarts\SagePaySuite\Model\SessionInterface::PRESAVED_PENDING_ORDER_KEY, null);
        $session->setData(\Ebizmarts\SagePaySuite\Model\SessionInterface::CONVERTING_QUOTE_TO_ORDER, 0);

        $this->redirect->redirect($this->response, "checkout/onepage/success");
        return $this->response;
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
