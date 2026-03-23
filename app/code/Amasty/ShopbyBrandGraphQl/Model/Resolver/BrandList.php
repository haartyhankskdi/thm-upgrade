<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Amasty Shop By Brand GraphQl for Magento 2 (System)
 */

namespace Amasty\ShopbyBrandGraphQl\Model\Resolver;

use Amasty\ShopbyBrand\Model\Attribute as AttributeProvider;
use Magento\Catalog\Model\Product;
use Magento\EavGraphQl\Model\Resolver\Query\Type;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Store\Model\ScopeInterface;
use Amasty\ShopbyBrand\Block\Widget\BrandList as BrandsWidget;
use Magento\Store\Model\StoreManagerInterface;

class BrandList implements ResolverInterface
{
    /**
     * @var BrandsWidget
     */
    private $brandList;

    /**
     * @var Type
     */
    private $type;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ArgumentResolver
     */
    private $argumentResolver;

    /**
     * @var AttributeProvider
     */
    private $attributeProvider;

    public function __construct(
        BrandsWidget $brandList,
        ScopeConfigInterface $scopeConfig,
        Type $type,
        AttributeProvider $attributeProvider,
        StoreManagerInterface $storeManager,
        ArgumentResolver $argumentResolver
    ) {
        $this->brandList = $brandList;
        $this->scopeConfig = $scopeConfig;
        $this->type = $type;
        $this->storeManager = $storeManager;
        $this->argumentResolver = $argumentResolver;
        $this->attributeProvider = $attributeProvider;
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
        $this->brandList->setData($this->argumentResolver->convertArgs($args));
        $this->prepareBrands($data);
        $this->prepareAttribute($data);

        $configValues = $this->scopeConfig->getValue(
            BrandsWidget::CONFIG_VALUES_PATH,
            ScopeInterface::SCOPE_STORE
        ) ?: [];

        $data['all_letters'] = implode(',', $this->brandList->getAllLetters());

        return array_merge($data, $configValues);
    }

    private function prepareBrands(array &$data): void
    {
        $this->brandList->initializeBlockConfiguration();
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        $brands = [];
        foreach ($this->brandList->getItems() as $brandItem) {
            $brand = $brandItem->getData();
            $brand['model'] = $brandItem;
            $brand['letter'] = $this->brandList->getLetter($brand['label']);
            $brand['image'] = str_replace($baseUrl, '', $brand['image'] ?: '');
            $brand['img'] = str_replace($baseUrl, '', $brand['img'] ?: '');
            $brand['url'] = str_replace($baseUrl, '', $brand['url'] ?: '');
            $brand['brandId'] = $brand['brand_id'] ?? null;
            $brands[] = $brand;
        }

        $data['items'] = $brands;
    }

    private function prepareAttribute(array &$data): void
    {
        $attribute = $this->attributeProvider->getAttribute();
        if ($attribute) {
            $data['brand_attribute']['attribute_code'] = $attribute->getAttributeCode();
            $data['brand_attribute']['input_type'] = $attribute->getFrontendInput();
            $data['brand_attribute']['attribute_type']
                = ucfirst($this->type->getType($attribute->getAttributeCode(), Product::ENTITY));
            $data['brand_attribute']['entity_type'] = Product::ENTITY;
        }
    }
}
