<?php

namespace Ebizmarts\BrippoPayments\Model\ConfigProvider;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Store\Model\StoreManagerInterface;
use Ebizmarts\BrippoPayments\Helper\Data as DataHelper;

class PaymentElementStandalone implements ConfigProviderInterface
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
            \Ebizmarts\BrippoPayments\Model\PaymentMethods\PaymentElementStandalone::METHOD_CODE
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
                \Ebizmarts\BrippoPayments\Model\PaymentMethods\PaymentElementStandalone::METHOD_CODE => [
                    'title' => $this->dataHelper->getStoreConfig(
                        \Ebizmarts\BrippoPayments\Model\PaymentMethods\PaymentElementStandalone::CONFIG_TITLE,
                        $scopeId
                    ),
                    'paymentMethod' => $this->dataHelper->getStoreConfig(
                        \Ebizmarts\BrippoPayments\Model\PaymentMethods\PaymentElementStandalone::CONFIG_PAYMENT_METHOD,
                        $scopeId
                    )
                ],
            ]
        ];
    }
}
