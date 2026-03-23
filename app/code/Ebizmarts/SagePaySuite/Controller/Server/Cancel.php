<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Ebizmarts\SagePaySuite\Controller\Server;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\Message\ManagerInterface;
use Ebizmarts\SagePaySuite\Model\Config;
use Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger;
use Ebizmarts\SagePaySuite\Model\RecoverCart;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteRepository;

class Cancel implements ActionInterface
{
    /**
     * Logging instance
     * @var Logger
     */
    private $suiteLogger;

    /** @var Config */
    private $config;

    /** @var EncryptorInterface */
    private $encryptor;

    /** @var RecoverCart */
    private $recoverCart;

    /** @var QuoteRepository */
    private $quoteRepository;
    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @var UrlInterface
     */
    private $url;
    /**
     * @var ManagerInterface
     */
    private $messageManager;
    /**
     * @var ResponseInterface
     */
    private $response;

    public function __construct(
        Logger $suiteLogger,
        Config $config,
        EncryptorInterface $encryptor,
        RecoverCart $recoverCart,
        QuoteRepository $quoteRepository,
        RequestInterface $request,
        UrlInterface $url,
        ManagerInterface $manager,
        ResponseInterface $response
    ) {
        $this->suiteLogger        = $suiteLogger;
        $this->config             = $config;
        $this->encryptor          = $encryptor;
        $this->recoverCart        = $recoverCart;
        $this->quoteRepository    = $quoteRepository;
        $this->request            = $request;
        $this->url                = $url;
        $this->messageManager     = $manager;
        $this->response           = $response;

        $this->config->setMethodCode(Config::METHOD_SERVER);
    }

    /**
     * @return ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $message = $this->getRequest()->getParam("message");
        $this->saveErrorMessage($message);

        $storeId = $this->getRequest()->getParam("_store");
        $quoteId = $this->encryptor->decrypt($this->getRequest()->getParam("quote"));
        $this->suiteLogger->debugLog($this->getRequest()->getParams(), [__METHOD__, __LINE__]);
        /** @var Quote $quote */

        try {
            $quote = $this->quoteRepository->get((int)$quoteId);
            $quote->setStoreId($storeId);
            $incrementId = $quote->getReservedOrderId();
            $this->suiteLogger->orderEndLog($incrementId, $quoteId);
        } catch (NoSuchEntityException $nsee) {
            $errorMessage = 'Quote with ID ' . $quoteId . ' not found.';
            $this->saveErrorMessage($errorMessage);
            $this->suiteLogger->logException(
                $nsee,
                [__METHOD__, __LINE__]
            );
        } catch (\Exception $e) {
            $errorMessage = $e;
            $this->saveErrorMessage($errorMessage);
            $this->suiteLogger->logException(
                $e,
                [__METHOD__, __LINE__]
            );
        }
        
        $this
            ->response
            ->setBody(
                '<script>window.top.location.href = "'
                . $this->getCancelUrl()
                . '";</script>'
            );

        return $this->response;
    }

    private function saveErrorMessage($errorMessage)
    {
        if (!empty($errorMessage)) {
             $this->messageManager->addErrorMessage($errorMessage);
        }
    }

    /**
     * @return string
     */
    public function getCancelUrl()
    {
        $params = [
            '_nosid' => true,
            '_secure' => true,
            '_store' => $this->config->getCurrentStoreId()
        ];
        return $this->url->getUrl('checkout/cart', $params);
    }
    protected function getRequest()
    {
        return $this->request;
    }
}
