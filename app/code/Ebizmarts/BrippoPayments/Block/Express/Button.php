<?php

namespace Ebizmarts\BrippoPayments\Block\Express;

use Ebizmarts\BrippoPayments\Helper\Logger;
use Ebizmarts\BrippoPayments\Model\Config\Source\ProductPageCategory;
use Ebizmarts\BrippoPayments\Model\Express;
use Ebizmarts\BrippoPayments\Helper\Express as ExpressHelper;
use Exception;
use Magento\CheckoutAgreements\Model\Agreement;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Ebizmarts\BrippoPayments\Helper\Data as DataHelper;

class Button extends Template
{
    /** @var DataHelper */
    protected $dataHelper;

    /** @var Express */
    protected $expressPaymentMethod;

    /** @var ExpressHelper */
    protected $expressHelper;

    /** @var Logger */
    protected $logger;

    /** @var Registry */
    protected $registry;

    /** @var Agreement */
    protected $checkoutAgreement;

    protected $scopeId;

    /**
     * @param Template\Context $context
     * @param DataHelper $dataHelper
     * @param Express $expressPaymentMethod
     * @param Logger $logger
     * @param Registry $registry
     * @param ExpressHelper $expressHelper
     * @param Agreement $checkoutAgreement
     * @param array $data
     * @throws NoSuchEntityException
     */
    public function __construct(
        Template\Context $context,
        DataHelper $dataHelper,
        Express $expressPaymentMethod,
        Logger $logger,
        Registry $registry,
        ExpressHelper $expressHelper,
        Agreement $checkoutAgreement,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->dataHelper = $dataHelper;
        $this->expressPaymentMethod = $expressPaymentMethod;
        $this->logger = $logger;
        $this->registry = $registry;
        $this->expressHelper = $expressHelper;
        $this->checkoutAgreement = $checkoutAgreement;
        $this->scopeId = $this->_storeManager->getStore()->getId();
    }

    /**
     * @param string $location
     * @return bool
     */
    public function getIsEnabled(string $location = ''): bool
    {
        try {
            if (!$this->expressPaymentMethod->isAvailable()) {
                return false;
            }

            if (!$this->dataHelper->isServiceReady($this->scopeId)) {
                return false;
            }

            if (empty($location)) {
                $location = $this->getRequest()->getFullActionName();
            }

            if (!$this->expressHelper->isAvailableAtLocation($this->scopeId, $location)) {
                return false;
            }

            try {
                if ($location === ExpressHelper::PRODUCT_PAGE_VIEW_NAME
                    && !$this->isAllowedForThisProduct($this->scopeId)) {
                    return false;
                }
            } catch (Exception $ex) {
                $this->logger->log($ex->getMessage());
            }

            return true;
        } catch (Exception $ex) {
            $this->logger->log($ex->getMessage());
            return false;
        }
    }

    /**
     * @param $config_path
     * @param $default
     * @return mixed|string
     */
    public function getLayoutPlacement($config_path, $default)
    {
        try {
            $layout_placement = $this->dataHelper->getStoreConfig(
                $config_path,
                $this->scopeId
            );
        } catch (Exception $ex) {
            $this->logger->log($ex->getMessage());
            $layout_placement = $default;
        }
        return $layout_placement;
    }

    /**
     * @param $config_path
     * @return bool
     */
    public function showOrSeparator($config_path):bool
    {
        $show_OR = true;
        try {
            $show_OR = $this->dataHelper->getStoreConfig(
                $config_path,
                $this->scopeId
            );
        } catch (Exception $ex) {
            $this->logger->log($ex->getMessage());
        }
        return (bool)$show_OR;
    }


    /**
     * @param $storeId
     * @return bool
     */
    private function isAllowedForThisProduct($storeId): bool
    {
        try {
            $minimumThreshold = $this->dataHelper->getStoreConfig(
                Express::XML_PATH_PRODUCT_MINIMUM_AMOUNT,
                $storeId
            );
            $maximumThreshold = $this->dataHelper->getStoreConfig(
                Express::XML_PATH_PRODUCT_MAXIMUM_AMOUNT,
                $storeId
            );
            if ($this->isProductThresholdCheckSupported()) {
                if (!empty($minimumThreshold) && is_numeric($minimumThreshold)) {
                    if ($this->getProductPrice() < $minimumThreshold) {
                        return false;
                    }
                }
                if (!empty($maximumThreshold) && is_numeric($maximumThreshold)) {
                    if ($this->getProductPrice() > $maximumThreshold) {
                        return false;
                    }
                }
            }

            $productPageCategories = $this->dataHelper->getStoreConfig(
                Express::XML_PATH_STORE_PRODUCT_PAGE_CATEGORIES,
                $storeId
            );

            if (!empty($productPageCategories) && $productPageCategories !== ProductPageCategory::ALL_CATEGRORIES) {
                $productCategoriesAllowed = explode(',', (string)$productPageCategories);
                $productCategories = $this->getProductCategories();
                if (!empty($productCategories)) {
                    $allowed = false;
                    $totalCategoriesConfigured = count($productCategoriesAllowed);
                    $totalCategoriesInProduct = count($productCategories);
                    for ($i = 0; $i < $totalCategoriesConfigured; $i++) {
                        $allowed = $this->isProductCategoryAllowed(
                            $totalCategoriesInProduct,
                            $productCategoriesAllowed[$i],
                            $productCategories
                        );
                        if ($allowed) {
                            break;
                        }
                    }
                    if (!$allowed) {
                        return false;
                    }
                }
            }

            return true;
        } catch (Exception $ex) {
            $this->logger->log($ex->getMessage());
            return true;
        }
    }

    /**
     * @param $totalCategoriesInProduct
     * @param $productCategoryAllowed
     * @param $productCategories
     * @return bool
     */
    private function isProductCategoryAllowed(
        $totalCategoriesInProduct,
        $productCategoryAllowed,
        $productCategories
    ): bool {
        $allowed = false;
        for ($i = 0; $i< $totalCategoriesInProduct; $i++) {
            if ($productCategoryAllowed === $productCategories[$i]) {
                $allowed = true;
                break;
            }
        }
        return $allowed;
    }

    /**
     * @return string
     */
    public function getPublishableKey(): string
    {
        try {
            return $this->dataHelper->getPlatformPublishableKey($this->scopeId);
        } catch (Exception $ex) {
            $this->logger->log($ex->getMessage());
            return '';
        }
    }

    /**
     * @return string|null
     */
    public function getAccountId(): ?string
    {
        try {
            return $this->dataHelper->getAccountId(
                $this->scopeId,
                $this->dataHelper->isLiveMode($this->scopeId)
            );
        } catch (Exception $ex) {
            $this->logger->log($ex->getMessage());
            return '';
        }
    }

    /**
     * @return string
     */
    public function getButtonType(): string
    {
        try {
            return $this->dataHelper->getStoreConfig(
                Express::XML_PATH_BUTTON_TYPE,
                $this->_storeManager->getStore()->getId()
            );
        } catch (Exception $ex) {
            $this->logger->log($ex->getMessage());
            return 'default';
        }
    }

    /**
     * @return string
     */
    public function getButtonTheme(): string
    {
        try {
            return $this->dataHelper->getStoreConfig(
                Express::XML_PATH_BUTTON_THEME,
                $this->_storeManager->getStore()->getId()
            );
        } catch (Exception $ex) {
            $this->logger->log($ex->getMessage());
            return 'dark';
        }
    }

    /**
     * @return string
     */
    public function getButtonHeight(): string
    {
        try {
            return $this->expressHelper->getExpressButtonHeight(
                $this->_storeManager->getStore()->getId()
            );
        } catch (Exception $ex) {
            $this->logger->log($ex->getMessage());
            return '50px';
        }
    }

    /**
     * @return mixed
     */
    public function getProductId()
    {
        $product = $this->registry->registry('product');
        return $product->getId();
    }

    /**
     * @return mixed|string
     */
    public function getDiscountInputNote()
    {
        try {
            return $this->dataHelper->getStoreConfig(
                Express::XML_PATH_COUPON_CODE_NOTE,
                $this->_storeManager->getStore()->getId()
            );
        } catch (Exception $ex) {
            $this->logger->log($ex->getMessage());
            return '';
        }
    }

    /**
     * @return mixed
     */
    public function getProductPrice()
    {
        $product = $this->registry->registry('product');
        return $product->getFinalPrice();
    }

    /**
     * @return bool
     */
    public function isProductThresholdCheckSupported(): bool
    {
        $product = $this->registry->registry('product');
        return $product->getTypeId() !== 'grouped' && $product->getTypeId() !== 'bundle';
    }

    /**
     * @return mixed
     */
    public function getProductCategories()
    {
        $product = $this->registry->registry('product');
        return $product->getCategoryIds();
    }

    /**
     * @return bool
     */
    public function getIsCouponCodeEnabled():bool
    {
        try {
            return $this->dataHelper->getStoreConfig(
                Express::XML_PATH_COUPON_CODE,
                $this->_storeManager->getStore()->getId()
            );
        } catch (Exception $ex) {
            $this->logger->log($ex->getMessage());
            return false;
        }
    }

    /**
     * @return array
     */
    public function getAgreements()
    {
        $agreements = [];
        try {
            if ($this->dataHelper->getStoreConfig(
                Express::XML_PATH_REQUEST_AGREEMENTS,
                $this->_storeManager->getStore()->getId()
            )) {
                $agreements = $this->checkoutAgreement->getCollection()
                    ->addStoreFilter($this->_storeManager->getStore()->getId())
                    ->addFieldToFilter('is_active', 1);
            }
        } catch (Exception $ex) {
            $this->logger->log($ex->getMessage());
        }

        return $agreements;
    }

    /**
     * @param $prefix
     * @return string
     */
    public function getUniqueElementId($prefix): string
    {
        return uniqid($prefix);
    }

    public function getMaximumThreshold($source)
    {
        $maximum_paths = [
            'cart' => Express::XML_PATH_CART_MAXIMUM_AMOUNT,
            'minicart' => Express::XML_PATH_MINICART_MAXIMUM_AMOUNT,
            'product_page' => Express::XML_PATH_PRODUCT_MAXIMUM_AMOUNT
        ];
        if (!array_key_exists($source, $maximum_paths)) {
            return 0;
        }
        $threshold = $this->dataHelper->getStoreConfig(
            $maximum_paths[$source],
            $this->scopeId
        );
        return (!empty($threshold) && is_numeric($threshold)) ? (float)$threshold * 100 : 0;
    }
    public function getMinimumThreshold($source)
    {
        $minimum_paths = [
            'cart' => Express::XML_PATH_CART_MINIMUM_AMOUNT,
            'minicart' => Express::XML_PATH_MINICART_MINIMUM_AMOUNT,
            'product_page' => Express::XML_PATH_PRODUCT_MINIMUM_AMOUNT
        ];
        if (!array_key_exists($source, $minimum_paths)) {
            return 0;
        }
        $threshold = $this->dataHelper->getStoreConfig(
            $minimum_paths[$source],
            $this->scopeId
        );
        return (!empty($threshold) && is_numeric($threshold)) ? (float)$threshold * 100 : 0;
    }
}
