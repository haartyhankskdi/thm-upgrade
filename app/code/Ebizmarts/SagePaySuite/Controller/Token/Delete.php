<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Controller\Token;

use Ebizmarts\SagePaySuite\Api\Data\ResultInterface;
use Ebizmarts\SagePaySuite\Api\TokenManagementInterface;
use Ebizmarts\SagePaySuite\Api\TokenManagementInterfaceFactory;
use Ebizmarts\SagePaySuite\Model\Config;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;
use Psr\Log\LoggerInterface;

class Delete implements ActionInterface
{
    /** @var LoggerInterface */
    private $logger;

    /** @var int */
    private $tokenId;

    /** @var string */
    private $paymentMethod;

    /** @var bool */
    private $isCustomerArea;

    /** @var Session */
    private $customerSession;

    /** @var TokenManagementInterfaceFactory */
    private $tokenManagementInterfaceFactory;

    /** @var RequestInterface */
    private $request;

    /** @var ManagerInterface */
    private $messageManager;

    /** @var RedirectInterface */
    private $redirect;

    /** @var ResponseInterface */
    private $response;

    /** @var ResultFactory */
    private $resultFactory;

    /**
     * Delete Constructor
     *
     * @param LoggerInterface $logger
     * @param Session $customerSession
     * @param RequestInterface $request
     * @param ManagerInterface $manager
     * @param RedirectInterface $redirect
     * @param ResponseInterface $response
     * @param ResultFactory $resultFactory
     * @param TokenManagementInterfaceFactory $tokenManagementInterfaceFactory
     */
    public function __construct(
        LoggerInterface $logger,
        Session $customerSession,
        RequestInterface $request,
        ManagerInterface $manager,
        RedirectInterface $redirect,
        ResponseInterface $response,
        ResultFactory $resultFactory,
        TokenManagementInterfaceFactory $tokenManagementInterfaceFactory
    ) {
        $this->logger = $logger;
        $this->customerSession = $customerSession;
        $this->request = $request;
        $this->messageManager = $manager;
        $this->redirect = $redirect;
        $this->response = $response;
        $this->resultFactory = $resultFactory;
        $this->tokenManagementInterfaceFactory = $tokenManagementInterfaceFactory;
        $this->isCustomerArea = true;
    }

    /**
     * @return bool|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            //get token id
            if (!empty($this->getRequest()->getParam("token_id"))) {
                $this->tokenId = $this->getRequest()->getParam("token_id");
                if (!empty($this->getRequest()->getParam("checkout"))) {
                    $this->isCustomerArea = false;
                    $this->paymentMethod = $this->getRequest()->getParam('pmethod');
                } else {
                    $isVault = $this->getRequest()->getParam('isVault');
                    if (isset($isVault) && $isVault) {
                        $this->paymentMethod = Config::METHOD_PI;
                    } else {
                        $this->paymentMethod = Config::METHOD_SERVER;
                    }
                }
            } else {
                throw new \Magento\Framework\Validator\Exception(__('Unable to delete token: Invalid token id.'));
            }

            $customerId = $this->customerSession->getCustomerId();
            /** @var TokenManagementInterface $tokenManagementInterface */
            $tokenManagementInterface = $this->tokenManagementInterfaceFactory->create();
            /** @var ResultInterface $result */
            $result = $tokenManagementInterface->deleteToken($this->tokenId, $customerId, $this->paymentMethod);
            if ($result->getSuccess()) {
                $responseContent = $this->getSuccessResponseContent();
            } else {
                $responseContent = $this->getFailResponseContent($result->getErrorMessage());
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);

            $responseContent = $this->getFailResponseContent($e->getMessage());
        }

        $resultJson = $this->getResultFactory();
        $resultJson->setData($responseContent);
        if ($this->isCustomerArea) {
            if ($responseContent["success"]) {
                $this->addSuccessMessage();
            } else {
                $this->messageManager->addErrorMessage(__($responseContent["error_message"]));
            }
            $this->redirect->redirect($this->response, 'elavon/customer/tokens');
        }
        return $resultJson;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function getResultFactory()
    {
        return $this->resultFactory->create(ResultFactory::TYPE_JSON);
    }

    public function addSuccessMessage()
    {
        $this->messageManager->addSuccessMessage(__('Token deleted successfully.'));
    }

    /**
     * @return array
     */
    private function getSuccessResponseContent()
    {
        return ['success' => true, 'response' => true];
    }

    /**
     * @param $errorMessage
     * @return array
     */
    private function getFailResponseContent($errorMessage)
    {
        return [
            'success' => false,
            'error_message' => __("Something went wrong: %1", $errorMessage),
        ];
    }

    /**
     * @return RequestInterface
     */
    protected function getRequest()
    {
        return $this->request;
    }
}
