<?php

namespace Ebizmarts\BrippoPayments\Controller\Adminhtml\Onboarding;

use Ebizmarts\BrippoPayments\Helper\Data;
use Ebizmarts\BrippoPayments\Helper\PlatformService\PlatformService;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Ebizmarts\BrippoPayments\Helper\Logger;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Ebizmarts\BrippoPayments\Plugin\CsrfFilter;

class Response extends Action
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @param Context $context
     * @param Logger $logger
     * @param StoreManagerInterface $storeManager
     * @param CsrfFilter $csrfFilter
     * @param Data $dataHelper
     */
    public function __construct(
        Context $context,
        Logger $logger,
        StoreManagerInterface $storeManager,
        CsrfFilter $csrfFilter,
        Data $dataHelper
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->dataHelper = $dataHelper;
        $csrfFilter->filterCrsfInterfaceImplementation($this);
    }

    public function execute()
    {
        $scopeParams = [];
        $detailsSubmitted = false;

        try {
            $scope = $this->getRequest()->getParam('scope');
            $scopeId = $this->getRequest()->getParam('scopeId');
            $stripeAccountId = $this->getRequest()->getParam('stripeAccountId');
            $liveMode = $this->getRequest()->getParam('liveMode') === 'live';
            $detailsSubmitted = $this->getRequest()->getParam('detailsSubmitted') === 'true';
            $redirect = empty($this->getRequest()->getParam('redirect'))
                ? null
                : base64_decode($this->getRequest()->getParam('redirect'));

            $this->logger->log('Received onboarding response: ' . $this->getRequest()->getParam('liveMode')
                . ' ' . $scope . ' ' . $scopeId . ' ' . $stripeAccountId);

            if ($scope === ScopeInterface::SCOPE_WEBSITES) {
                $scopeParams['website'] = $this->storeManager->getStore($scopeId)->getWebsiteId();
            } elseif ($scope === ScopeInterface::SCOPE_STORES) {
                $scopeParams['store'] = $scopeId;
            }

            $this->dataHelper->saveAccountId($stripeAccountId, $scope, $scopeId, $liveMode);
            $this->dataHelper->saveLiveMode($liveMode, $scope, $scopeId);
            $this->dataHelper->cacheManager->flush($this->dataHelper->cacheManager->getAvailableTypes());
            $this->dataHelper->cacheManager->clean($this->dataHelper->cacheManager->getAvailableTypes());
        } catch (Exception $ex) {
            $this->messageManager->addErrorMessage($ex->getMessage());
            $this->logger->log($ex->getMessage());
        }

        if ($detailsSubmitted) {
            $this->messageManager->addSuccessMessage(__('Brippo was successfully set up.'));
            if (!empty($redirect)) {
                return $this->resultRedirectFactory->create()->setUrl($redirect);
            } else {
                return $this->resultRedirectFactory->create()->setPath(
                    'adminhtml/system_config/edit/section/payment',
                    $scopeParams
                );
            }
        } else {
            return $this->resultRedirectFactory->create()->setUrl(
                PlatformService::SERVICE_URL . 'accounts/dashboard/submit-details/'
                . 'magento/'
                . (!empty($redirect)
                    ? urlencode($redirect)
                    : urlencode(
                        $this->_url->getUrl(
                            'adminhtml/system_config/edit/section/payment',
                            $scopeParams
                        )
                    )
                )
            );
        }
    }

    protected function _isAllowed(): bool
    {
        return true;
    }

    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
