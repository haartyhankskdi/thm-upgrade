<?php

namespace Ebizmarts\BrippoPayments\Block;

use Ebizmarts\BrippoPayments\Helper\Data as DataHelper;
use Ebizmarts\BrippoPayments\Helper\Logger;
use Exception;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\App\ObjectManager;

class Head extends Template
{
    /** @var DataHelper */
    protected $dataHelper;

    /** @var RequestInterface */
    protected $request;

    /** @var Logger */
    protected $logger;

    /** @var mixed|null */
    public $cspNonceProvider;

    public function __construct(
        Template\Context $context,
        DataHelper $dataHelper,
        RequestInterface $request,
        Logger $logger,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->dataHelper = $dataHelper;
        $this->request = $request;
        $this->logger = $logger;
        $this->cspNonceProvider = $this->getCspNonceProvider();
    }

    /**
     * Get CSP Nonce Provider with lazy loading for backward compatibility
     * 
     * @return mixed|null
     */
    private function getCspNonceProvider()
    {
        try {
            // Check if CspNonceProvider class exists (Magento 2.4.7+)
            if (class_exists('\Magento\Csp\Helper\CspNonceProvider')) {
                $objectManager = ObjectManager::getInstance();
                return $objectManager->get('\Magento\Csp\Helper\CspNonceProvider');
            }
        } catch (Exception $e) {
            // Silently fail for older Magento versions
        }
        
        return null;
    }

    /**
     * @return bool
     */
    public function isBrippoActive(): bool
    {
        try {
            $storeId = $this->_storeManager->getStore()->getId();

            if (!$this->dataHelper->isServiceReady($storeId)) {
                return false;
            }
        } catch (Exception $ex) {
            return false;
        }

        return true;
    }

    public function isRecoverCheckout(): bool
    {
        return $this->request->getFullActionName() === 'brippo_payments_payments_recover';
    }

    /**
     * Get the configuration URL for Brippo payments
     *
     * @return string
     */
    public function getConfigurationUrl(): string
    {
        return $this->getUrl('brippo_payments/payments/configuration');
    }
}
