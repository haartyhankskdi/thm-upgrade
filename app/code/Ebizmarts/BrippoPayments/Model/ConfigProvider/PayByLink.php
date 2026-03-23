<?php

namespace Ebizmarts\BrippoPayments\Model\ConfigProvider;

use Ebizmarts\BrippoPayments\Helper\Data as DataHelper;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Store\Model\StoreManagerInterface;
use Ebizmarts\BrippoPayments\Model\PayByLink as PayByLinkMethod;

class PayByLink implements ConfigProviderInterface
{
    /**
     * @var PayByLinkMethod
     */
    private $method;

    /**
     * @var DataHelper
     */
    private $dataHelper;

    protected $storeManager;

    /**
     * @param PaymentHelper $paymentHelper
     * @param DataHelper $dataHelper
     * @param StoreManagerInterface $storeManager
     * @throws LocalizedException
     */
    public function __construct(
        PaymentHelper $paymentHelper,
        DataHelper $dataHelper,
        StoreManagerInterface $storeManager
    ) {

        $this->method = $paymentHelper->getMethodInstance(
            PayByLinkMethod::METHOD_CODE
        );
        $this->dataHelper = $dataHelper;
        $this->storeManager = $storeManager;
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
                'brippo_paybylink' => [
                    'isEnabled' => $this->method->isAvailable(),
                    'checkoutNote' => $this->dataHelper->getStoreConfig(
                        PayByLinkMethod::XML_PATH_STORE_CONFIG_NOTE,
                        $scopeId
                    ),
                    'payment_option_logos' => $this->dataHelper->getStoreConfig(
                        'payment/brippo_payments_paybylink/payment_option_logos',
                        $scopeId
                    )
                ]
            ]
        ];
    }
}
