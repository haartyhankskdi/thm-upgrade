<?php

namespace Ebizmarts\BrippoPayments\Controller\Adminhtml\Domains;

use Ebizmarts\BrippoPayments\Helper\Data;
use Ebizmarts\BrippoPayments\Helper\Logger;
use Ebizmarts\BrippoPayments\Helper\ApplePay as ApplePayHelper;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Store\Model\StoreManagerInterface;

class RegisterStoreDomain extends Action
{
    /**
     * @var ApplePayHelper
     */
    protected $applePayHelper;
    /**
     * @var JsonFactory
     */
    protected $jsonFactory;
    /**
     * @var Logger
     */
    protected $logger;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    private $dataHelper;

    public function __construct(
        Context $context,
        ApplePayHelper $applePayHelper,
        Logger $logger,
        JsonFactory $jsonFactory,
        StoreManagerInterface $storeManager,
        Data $dataHelper
    ) {
        parent::__construct($context);
        $this->applePayHelper = $applePayHelper;
        $this->logger = $logger;
        $this->jsonFactory = $jsonFactory;
        $this->storeManager = $storeManager;
        $this->dataHelper = $dataHelper;
    }

    public function execute()
    {
        $scope = $this->dataHelper->getScopeTypeFromUrl();
        $scopeId = $this->dataHelper->getScopeIdFromUrl();

        $resultJson = $this->jsonFactory->create();
        $response = [];
        foreach ($this->getAllUrls() as $url) {
            try {
                $this->applePayHelper->registerStoreDomain($url, $scopeId, $scope);
                $response[] = [
                    "url" => $url,
                    "valid" => 1
                ];
            } catch (Exception $e) {
                $response[] = [
                    "url" => $url,
                    "valid" => 0,
                    "message" => $e->getMessage()
                ];
            }
        }
        return $resultJson->setData($response);
    }

    /**
     * @return array
     */
    private function getAllUrls(): array
    {
        $urls = [];
        foreach ($this->storeManager->getStores() as $store) {
            $urls[] = explode('/', (string)$store->getBaseUrl())[2];
        }

        return array_unique($urls);
    }
}
