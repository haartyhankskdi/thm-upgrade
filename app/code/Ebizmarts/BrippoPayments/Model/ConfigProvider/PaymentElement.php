<?php

namespace Ebizmarts\BrippoPayments\Model\ConfigProvider;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Store\Model\StoreManagerInterface;
use Ebizmarts\BrippoPayments\Helper\Data as DataHelper;

class PaymentElement implements ConfigProviderInterface
{
    protected $method;
    protected $storeManager;
    protected $dataHelper;

    /**
     * @param PaymentHelper $paymentHelper
     * @param StoreManagerInterface $storeManager
     * @param DataHelper $dataHelper
     * @throws LocalizedException
     */
    public function __construct(
        PaymentHelper $paymentHelper,
        StoreManagerInterface $storeManager,
        DataHelper $dataHelper
    ) {

        $this->method = $paymentHelper->getMethodInstance(
            \Ebizmarts\BrippoPayments\Model\PaymentElement::METHOD_CODE
        );
        $this->storeManager = $storeManager;
        $this->dataHelper = $dataHelper;
    }

    /**
     * @throws NoSuchEntityException
     */
    public function getConfig()
    {
        if (!$this->method->isAvailable()) {
            return [];
        }

        $scopeId = $this->storeManager->getStore()->getId();

        return [
            'payment' => [
                \Ebizmarts\BrippoPayments\Model\PaymentElement::METHOD_CODE => [
                    'pKey' => $this->dataHelper->getPlatformPublishableKey(
                        $scopeId
                    ),
                    'businessName' => $this->dataHelper->getStoreDomain(),
                    'layout' => $this->dataHelper->getStoreConfig(
                        \Ebizmarts\BrippoPayments\Model\PaymentElement::XML_PATH_LAYOUT,
                        $scopeId
                    ),
                    'includeWallets' => $this->dataHelper->getStoreConfig(
                        \Ebizmarts\BrippoPayments\Model\PaymentElement::XML_PATH_INCLUDE_WALLETS,
                        $scopeId
                    ),
                    'payment_option_logos' => $this->dataHelper->getStoreConfig(
                        \Ebizmarts\BrippoPayments\Model\PaymentElement::XML_PATH_DISPLAY_PM_LOGOS,
                        $scopeId
                    ),
                ],
            ]
        ];
    }
}
