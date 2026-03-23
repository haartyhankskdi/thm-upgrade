<?php

namespace Ebizmarts\BrippoPayments\Controller\Adminhtml\Terminals;

use Ebizmarts\BrippoPayments\Helper\BrippoApi\Terminals;
use Ebizmarts\BrippoPayments\Helper\Data;
use Ebizmarts\BrippoPayments\Helper\Logger;
use Ebizmarts\BrippoPayments\Helper\Stripe;
use Ebizmarts\BrippoPayments\Helper\TerminalBackend;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;

class Process extends Action
{
    protected $dataHelper;
    protected $logger;
    protected $jsonFactory;
    protected $brippoApiTerminals;
    protected $storeManager;
    protected $brippoTerminalHelper;

    /**
     * @param Context $context
     * @param Logger $logger
     * @param Data $dataHelper
     * @param JsonFactory $jsonFactory
     * @param Terminals $brippoApiTerminals
     * @param StoreManagerInterface $storeManager
     * @param TerminalBackend $brippoTerminalHelper
     */
    public function __construct(
        Context $context,
        Logger $logger,
        Data $dataHelper,
        JsonFactory  $jsonFactory,
        Terminals $brippoApiTerminals,
        StoreManagerInterface $storeManager,
        TerminalBackend $brippoTerminalHelper
    ) {
        parent::__construct($context);
        $this->dataHelper = $dataHelper;
        $this->logger = $logger;
        $this->jsonFactory = $jsonFactory;
        $this->brippoApiTerminals = $brippoApiTerminals;
        $this->storeManager = $storeManager;
        $this->brippoTerminalHelper = $brippoTerminalHelper;
    }

    public function execute()
    {
        try {
            $requestBody = $this->dataHelper->unserializeRequestBody($this->getRequest());

            $readerId = isset($requestBody['readerId']) ? $requestBody['readerId'] : null;
            if (empty($readerId)) {
                throw new LocalizedException(__('Reader ID not found'));
            }

            $paymentIntentId = isset($requestBody['paymentIntentId']) ? $requestBody['paymentIntentId'] : null;
            if (empty($paymentIntentId)) {
                throw new LocalizedException(__('Payment intent ID not found'));
            }

            $cardInputMethod = isset($requestBody['cardInputMethod'])
                ? $requestBody['cardInputMethod'] : Stripe::PARAM_MOTO;

            $scopeId = $this->storeManager->getStore()->getId();
            $liveMode = $this->dataHelper->isLiveMode($scopeId);

            $response = [
                'valid' => 1,
                'data' => $this->brippoApiTerminals->processPaymentIntent(
                    $liveMode,
                    $readerId,
                    $paymentIntentId,
                    $cardInputMethod === Stripe::PARAM_MOTO
                )
            ];
        } catch (Exception $ex) {
            $response = [
                'valid' => 0,
                'message' => $this->brippoTerminalHelper->prettifyErrorMessage($ex->getMessage())
            ];
            $this->logger->log($ex->getMessage());
        }

        $resultJson = $this->jsonFactory->create();
        $resultJson->setData($response);
        return $resultJson;
    }

    protected function _isAllowed()
    {
        return true;
    }
}
