<?php

namespace Ebizmarts\BrippoPayments\Model\ConfigProvider;

use Ebizmarts\BrippoPayments\Helper\Data as DataHelper;
use Ebizmarts\BrippoPayments\Model\Config\Source\ExpressLocation;
use Ebizmarts\BrippoPayments\Model\Express;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Helper\Data as PaymentHelper;
use Ebizmarts\BrippoPayments\Helper\Express as ExpressHelper;
use Magento\Store\Model\StoreManagerInterface;

class ExpressCheckout implements ConfigProviderInterface
{
    /**
     * @var Express
     */
    private $method;

    /**
     * @var ExpressHelper
     */
    private $expressHelper;

    /**
     * @var DataHelper
     */
    private $dataHelper;

    protected $storeManager;

    /**
     * @param PaymentHelper $paymentHelper
     * @param ExpressHelper $expressHelper
     * @param DataHelper $dataHelper
     * @param StoreManagerInterface $storeManager
     * @throws LocalizedException
     */
    public function __construct(
        PaymentHelper $paymentHelper,
        ExpressHelper $expressHelper,
        DataHelper $dataHelper,
        StoreManagerInterface $storeManager
    ) {

        $this->method = $paymentHelper->getMethodInstance(
            Express::METHOD_CODE
        );
        $this->expressHelper = $expressHelper;
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
                'stripeconnect_express' => [
                    'isEnabled' => $this->method->isAvailable(),
                    'enabledInCheckout' => $this->expressHelper->isAvailableAtLocation(
                        $scopeId,
                        'checkout_index_index'
                    ),
                    'checkoutLocation' => $this->dataHelper->getStoreConfig(
                        Express::XML_PATH_CHECKOUT_LOCATION,
                        $scopeId
                    ),
                    'walletsLogosUrls' => $this->expressHelper->getWalletsLogos(),
                    'accountId' => $this->dataHelper->getAccountId(
                        $scopeId,
                        $this->dataHelper->isLiveMode($scopeId)
                    ),
                    'pKey' => $this->dataHelper->getPlatformPublishableKey(
                        $scopeId
                    ),
                    'buttonType' => $this->dataHelper->getStoreConfig(Express::XML_PATH_BUTTON_TYPE, $scopeId),
                    'buttonTheme' => $this->dataHelper->getStoreConfig(Express::XML_PATH_BUTTON_THEME, $scopeId),
                    'buttonHeight' => $this->expressHelper->getExpressButtonHeight($scopeId),
                    'source' => ExpressLocation::CHECKOUT,
                    'checkoutButton' => $this->dataHelper->getStoreConfig(
                        Express::XML_PATH_CHECKOUT_BUTTON,
                        $scopeId
                    ),
                    'layoutPlacementSelector' => $this->dataHelper->getStoreConfig(
                        Express::XML_PATH_CHECKOUT_PLACEMENT,
                        $scopeId
                    ),
                    'validationMode' => $this->dataHelper->getStoreConfig(
                        Express::XML_PATH_CHECKOUT_VALIDATION_MODE,
                        $scopeId
                    )
                ]
            ]
        ];
    }
}
