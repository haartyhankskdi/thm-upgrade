<?php

namespace Ebizmarts\BrippoPayments\Controller\Adminhtml\Onboarding;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Ebizmarts\BrippoPayments\Helper\Logger;
use Ebizmarts\BrippoPayments\Helper\Data as DataHelper;

class Reset extends Action
{
    protected $jsonFactory;
    protected $logger;
    protected $dataHelper;

    public function __construct(
        Context      $context,
        JsonFactory  $jsonFactory,
        Logger       $logger,
        DataHelper   $dataHelper
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->logger = $logger;
        $this->dataHelper = $dataHelper;
    }

    public function execute()
    {
        $scope = $this->getRequest()->getParam('scope');
        $scopeId = $this->getRequest()->getParam('scopeId');
        $liveMode = $this->getRequest()->getParam('liveMode');

        try {
            $this->dataHelper->saveAccountId('', $scope, $scopeId, $liveMode);
            $this->dataHelper->cacheManager->flush($this->dataHelper->cacheManager->getAvailableTypes());
            $this->dataHelper->cacheManager->clean($this->dataHelper->cacheManager->getAvailableTypes());

            $response = [
                'valid' => 1
            ];
        } catch (Exception $ex) {
            $response = [
                'valid' => 0,
                'message' => $ex->getMessage()
            ];
            $this->logger->log($ex->getMessage());
        }

        $resultJson = $this->jsonFactory->create();
        $resultJson->setData($response);
        return $resultJson;
    }
}
