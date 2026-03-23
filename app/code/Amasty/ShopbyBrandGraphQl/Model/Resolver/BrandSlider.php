<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Amasty Shop By Brand GraphQl for Magento 2 (System)
 */

namespace Amasty\ShopbyBrandGraphQl\Model\Resolver;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Store\Model\ScopeInterface;
use Amasty\ShopbyBrand\Block\Widget\BrandSlider as BrandsWidget;
use Magento\Store\Model\StoreManagerInterface;

class BrandSlider implements ResolverInterface
{
    /**
     * @var BrandsWidget
     */
    private $brandSlider;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ArgumentResolver
     */
    private $argumentResolver;

    public function __construct(
        BrandsWidget $brandSlider,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        ArgumentResolver $argumentResolver
    ) {
        $this->brandSlider = $brandSlider;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->argumentResolver = $argumentResolver;
    }

    /**
     * @param Field $field
     * @param \Magento\Framework\GraphQl\Query\Resolver\ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array|\Magento\Framework\GraphQl\Query\Resolver\Value|mixed
     * @throws \Exception
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ) {
        $data = [];
        $this->brandSlider->setData($this->argumentResolver->convertArgs($args));
        $this->prepareBrands($data);

        $configValues = $this->scopeConfig->getValue(
            BrandsWidget::CONFIG_VALUES_PATH,
            ScopeInterface::SCOPE_STORE
        ) ?: [];

        return array_merge($data, $configValues);
    }

    private function prepareBrands(array &$data): void
    {
        $this->brandSlider->initializeBlockConfiguration();
        $brands = [];
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        foreach ($this->brandSlider->getItems() as $brandItem) {
            $brand = $brandItem->getData();
            $brand['model'] = $brandItem;
            $brand['img'] = str_replace($baseUrl, '', $brand['img'] ?: '');
            $brand['url'] = str_replace($baseUrl, '', $brand['url'] ?: '');
            $brands[] = $brand;
        }

        $data['items'] = $brands;
    }
}
