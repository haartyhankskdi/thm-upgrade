<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand Subscription Functionality
 */

namespace Amasty\ShopByBrandSubscriptionFunctionality\Block\Widget;

use Amasty\ShopByBrandSubscriptionFunctionality\Model\Source\WidgetBrandFields;
use Amasty\ShopbyBase\Api\Data\OptionSettingInterface;
use Amasty\ShopbyBase\Helper\OptionSetting;
use Amasty\ShopbyBase\Model\OptionSetting as OptionSettingModel;
use Amasty\ShopbyBase\ViewModel\OptionsDataBuilder;
use Amasty\ShopbyBrand\Model\BrandResolver;
use Amasty\ShopbyBrand\Model\ConfigProvider;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Widget\Block\BlockInterface;

class BrandInfo extends Template implements BlockInterface, IdentityInterface
{
    private const BRAND_FIELDS = 'brand_fields';

    /**
     * @var string
     */
    protected $_template = "widget/info.phtml";

    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * @var BrandResolver
     */
    private $brandResolver;

    /**
     * @var OptionSetting
     */
    private $optionSetting;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var OptionsDataBuilder
     */
    private $optionsDataBuilder;

    /**
     * @var OptionSettingInterface[]
     */
    private $brands = [];

    public function __construct(
        StoreManagerInterface $storeManager,
        ConfigProvider $configProvider,
        OptionSetting $optionSetting,
        BrandResolver $brandResolver,
        OptionsDataBuilder $optionsDataBuilder,
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->_registry = $registry;
        $this->brandResolver = $brandResolver;
        $this->optionSetting = $optionSetting;
        $this->configProvider = $configProvider;
        $this->optionsDataBuilder = $optionsDataBuilder;
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    public function getCurrentProduct()
    {
        return $this->_registry->registry('current_product');
    }

    public function getIdentities(): array
    {
        $identities = [];
        foreach ($this->getBrands() as $brand) {
            $identities[] = OptionSettingModel::CACHE_TAG . '_' . $brand->getValue();
        }

        return $identities;
    }

    /**
     * @throws NoSuchEntityException
     * @return OptionSettingInterface[]
     */
    public function getBrands(): array
    {
        if ($this->brands) {
            return $this->brands;
        }

        try {
            $brandAttributeCode = $this->configProvider->getBrandAttributeCode();
            $brandValue = $this->optionsDataBuilder->getAttributeValues(
                $this->getCurrentProduct(),
                [$brandAttributeCode]
            );
            if ($brandValue) {
                $storeId = $this->storeManager->getStore()->getId();
                foreach ($brandValue as $value) {
                    $this->brands[] = $this->optionSetting->getSettingByOption(
                        (int)$value,
                        (string)$brandAttributeCode,
                        (int)$storeId
                    );
                }
            }
        } catch (\Throwable $e) {
            $this->_logger->critical($e);
        }

        return $this->brands;
    }

    public function shouldShowBrandInfo(OptionSettingInterface $brand): bool
    {
        $brandFields = $this->getData(self::BRAND_FIELDS);

        return (!$brandFields || strpos($brandFields, WidgetBrandFields::ADDITIONAL_BRAND_INFORMATION) !== false)
                && $this->getBrandInfoValues($brand);
    }

    public function shouldShowBrandDescription(OptionSettingInterface $brand): bool
    {
        $brandFields = $this->getData(self::BRAND_FIELDS);

        return (!$brandFields || strpos($brandFields, WidgetBrandFields::SHOW_DESCRIPTION) !== false)
                && $brand->getShortDescription();
    }

    public function isProductPage(): bool
    {
        return (bool)$this->getCurrentProduct();
    }

    /**
     * @return string[]
     */
    public function getBrandInfoValues(OptionSettingInterface $brand): array
    {
        if (!$brand) {
            return [];
        }

        return array_filter([
            $brand->getBrandInfoPostalAddress(),
            $brand->getBrandInfoElectronicAddress(),
            $brand->getBrandInfoContact()
        ], static function (?string $value) {
            return $value !== null && !empty(trim($value));
        });
    }
}
