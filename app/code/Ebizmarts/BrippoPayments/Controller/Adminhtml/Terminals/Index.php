<?php

namespace Ebizmarts\BrippoPayments\Controller\Adminhtml\Terminals;

use Ebizmarts\BrippoPayments\Helper\BrippoApi\Terminals;
use Ebizmarts\BrippoPayments\Helper\Data;
use Ebizmarts\BrippoPayments\Helper\Logger;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Store\Model\StoreManagerInterface;

class Index extends Action
{
    protected $dataHelper;
    protected $logger;
    protected $jsonFactory;
    protected $brippoApiTerminals;
    protected $storeManager;

    /**
     * @param Context $context
     * @param Logger $logger
     * @param Data $dataHelper
     * @param JsonFactory $jsonFactory
     * @param Terminals $brippoApiTerminals
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        Logger $logger,
        Data $dataHelper,
        JsonFactory  $jsonFactory,
        Terminals $brippoApiTerminals,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->dataHelper = $dataHelper;
        $this->logger = $logger;
        $this->jsonFactory = $jsonFactory;
        $this->brippoApiTerminals = $brippoApiTerminals;
        $this->storeManager = $storeManager;
    }

    public function execute()
    {
        try {
            $scopeId = $this->storeManager->getStore()->getId();
            $liveMode = $this->dataHelper->isLiveMode($scopeId);
            $response = [
                'valid' => 1,
                'locations' => $this->brippoApiTerminals->list(
                    $liveMode,
                    $this->dataHelper->getAccountId(
                        $scopeId,
                        $liveMode
                    )
                )
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
