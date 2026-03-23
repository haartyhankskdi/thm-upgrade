<?php
/**
 * Copyright © 2015 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Controller\Adminhtml\Form;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger;

class Failure implements ActionInterface
{
    /**
     * Logging instance
     * @var \Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger
     */
    private $suiteLogger;

    /**
     * @var \Ebizmarts\SagePaySuite\Model\Form
     */
    private $formModel;
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
     * @var RequestInterface
     */
    private $request;

    /**
     * @param Logger $suiteLogger
     * @param \Ebizmarts\SagePaySuite\Model\Form $formModel
     * @param ManagerInterface $manager
     * @param RedirectInterface $redirect
     * @param ResponseInterface $response
     * @param RequestInterface $request
     */
    public function __construct(
        \Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger $suiteLogger,
        \Ebizmarts\SagePaySuite\Model\Form $formModel,
        ManagerInterface $manager,
        RedirectInterface $redirect,
        ResponseInterface $response,
        RequestInterface $request
    ) {

        $this->suiteLogger      = $suiteLogger;
        $this->formModel        = $formModel;
        $this->messageManager   = $manager;
        $this->redirect         = $redirect;
        $this->response         = $response;
        $this->request          = $request;
    }

    /**
     * @return ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            //decode response
            $response = $this->formModel->decodeSagePayResponse($this->getRequest()->getParam("crypt"));
            if (!isset($response["Status"]) || !isset($response["StatusDetail"])) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Invalid response from Opayo'));
            }
            //log response
            $this->suiteLogger->sageLog(Logger::LOG_REQUEST, $response, [__METHOD__, __LINE__]);

            $statusDetail = $response["StatusDetail"];
            $statusDetail = explode(" : ", $statusDetail);
            $statusDetail = $statusDetail[1];
            $this->messageManager->addErrorMessage($response["Status"] . ": " . $statusDetail);
            $this->redirect->redirect($this->response, 'sales/order_create/index');
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->suiteLogger->logException($e, [__METHOD__, __LINE__]);
        }
        return $this->response;
    }

    public function getRequest()
    {
        return $this->request;
    }
}
