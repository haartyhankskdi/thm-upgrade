<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand for Magento 2
 */

namespace Amasty\ShopbyBrand\Model\Customizer\Category;

use Amasty\ShopbyBase\Api\CategoryDataSetterInterface;
use Amasty\ShopbyBase\Model\Category\Manager as CategoryManager;
use Amasty\ShopbyBase\Model\Customizer\Category as CategoryCustomizer;
use Amasty\ShopbyBase\Model\Customizer\Category\CustomizerInterface;
use Amasty\ShopbyBase\Model\UrlBuilder;
use Amasty\ShopbyBrand\Model\BrandResolver;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product\ProductList\Toolbar;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Module\Manager as ModuleManager;

class Brand implements CustomizerInterface
{
    public const APPLY_TO_HEADING = 'am_apply_to_heading';
    public const APPLY_TO_META = 'am_apply_to_meta';

    public const CATEGORY_DISPLAY_MODE_KEY = 'display_mode';

    /**
     * @var string[]
     */
    private array $excludedParams = [
        'product_list_mode',
        'product_list_order',
        'product_list_dir',
        'product_list_limit'
    ];

    /**
     * @var  Category
     */
    private $category;

    /**
     * @var BrandResolver
     */
    private $brandResolver;

    /**
     * @var UrlBuilder
     */
    private UrlBuilder $url;

    /**
     * @var ModuleManager
     */
    private ModuleManager $moduleManager;

    public function __construct(
        BrandResolver $brandResolver,
        ?UrlBuilder $url = null,
        ?ModuleManager $moduleManager = null
    ) {
        $this->brandResolver = $brandResolver;
        $this->url = $url ?? ObjectManager::getInstance()->get(UrlBuilder::class);
        $this->moduleManager = $moduleManager ?? ObjectManager::getInstance()->get(ModuleManager::class);
    }

    /**
     * @param Category $category
     * @return $this
     */
    public function prepareData(Category $category)
    {
        $brand = $this->brandResolver->getCurrentBrand();
        if (!$brand) {
            return $this;
        }

        $this->category = $category;

        $data = $this->getOptionData();

        $this->setTitle($data['title'])
            ->setDescription($data['description'])
            ->setImg($data['img_url'])
            ->setCmsBlock($data['cms_block'])
            ->setMetaTitle($data['meta_title'])
            ->setMetaDescription($data['meta_description'])
            ->setMetaKeywords($data['meta_keywords'])
            ->setBottomCmsBlock($data['bottom_cms_block'])
            ->setDisplayMode();
        $category->setData(CategoryDataSetterInterface::APPLIED_BRAND_VALUE, $brand->getValue());

        if (!$this->moduleManager->isEnabled('Amasty_ShopbySeo')) {
            $canonical = $this->url->getCurrentUrl(false);
            $canonical = $this->prepareCanonicalUrl($canonical);

            $category->setData(CategoryCustomizer::ORIGINAL_CATEGORY_URL, $category->getUrl());
            $category->setData('url', $canonical);
        }

        return $this;
    }

    private function prepareCanonicalUrl(string $url): string
    {
        $pos = max(0, strpos($url, '?'));

        if ($pos) {
            $urlParts = explode('?', $url);
            if (isset($urlParts[0])) {
                $url = $urlParts[0];
                if (isset($urlParts[1])) {
                    // @codingStandardsIgnoreLine
                    parse_str($urlParts[1], $params);
                    foreach ($this->excludedParams as $param) {
                        unset($params[$param]);
                    }
                    if (isset($params[Toolbar::PAGE_PARM_NAME]) && $params[Toolbar::PAGE_PARM_NAME] <= 1) {
                        unset($params[Toolbar::PAGE_PARM_NAME]);
                    }
                    if ($params) {
                        $url .= '?' . http_build_query($params);
                    }
                }
            }
        }

        return $url;
    }

    /**
     * @return array
     */
    private function getOptionData()
    {
        $result = [
            'title' => [],
            'description' => [],
            'cms_block' => null,
            'img_url' => null,
            'meta_title' => [],
            'meta_description' => [],
            'meta_keywords' => [],
            'bottom_cms_block' => null
        ];

        $setting = $this->brandResolver->getCurrentBrand();

        if ($setting->getTitle()) {
            $result['title'][] = $setting->getTitle();
        }
        if ($setting->getDescription()) {
            $result['description'][] = $setting->getDescription(true);
        }
        if ($setting->getTopCmsBlockId() && $result['cms_block'] === null) {
            $result['cms_block'] = $setting->getTopCmsBlockId();
        }
        if ($setting->getBottomCmsBlockId() && $result['bottom_cms_block'] === null) {
            $result['bottom_cms_block'] = $setting->getBottomCmsBlockId();
        }
        if ($setting->getImageUrl() && $result['img_url'] === null) {
            $result['img_url'] = $setting->getImageUrl();
        }

        if ($setting->getMetaTitle()) {
            $result['meta_title'][] = $setting->getMetaTitle();
        }
        if ($setting->getMetaDescription()) {
            $result['meta_description'][] = $setting->getMetaDescription();
        }
        if ($setting->getMetaKeywords()) {
            $result['meta_keywords'][] = $setting->getMetaKeywords();
        }

        return $result;
    }

    /**
     * Set category title.
     * @param array $title
     * @return $this
     */
    private function setTitle($title)
    {
        if ($title) {
            $this->category->setName(join('', $title));
        }

        return $this;
    }

    /**
     * Set category meta title.
     * @param array $metaTitle
     * @return $this
     */
    private function setMetaTitle($metaTitle)
    {
        if ($metaTitle) {
            $this->category->setData('meta_title', join('', $metaTitle));
        }

        return $this;
    }

    /**
     * Set category description.
     * @param array $description
     * @return $this
     */
    private function setDescription($description)
    {
        if ($description) {
            $description = '<span class="amshopby-descr">' . join('<br>', $description) . '</span>';
            $this->category->setData('description', $description);
        }
        return $this;
    }

    /**
     * Set category meta description.
     * @param array $metaDescription
     * @return $this
     */
    private function setMetaDescription(array $metaDescription)
    {
        if ($metaDescription) {
            $this->category->setData('meta_description', join('', $metaDescription));
        }

        return $this;
    }

    /**
     * Set category meta keywords.
     * @param array $metaKeywords
     * @return $this
     */
    private function setMetaKeywords($metaKeywords)
    {
        if ($metaKeywords) {
            $this->category->setData('meta_keywords', join('', $metaKeywords));
        }

        return $this;
    }

    /**
     * Set category image.
     * @param string|null $imgUrl
     * @return $this
     */
    private function setImg($imgUrl)
    {
        if ($imgUrl !== null) {
            $this->category->setData(CategoryManager::CATEGORY_SHOPBY_IMAGE_URL, $imgUrl);
        }
        return $this;
    }

    /**
     * Set category CMS block.
     * @param string|null $blockId
     * @return $this
     */
    private function setCmsBlock($blockId)
    {
        if ($blockId !== null) {
            $this->category->setData('landing_page', $blockId);
            $this->category->setData(CategoryManager::CATEGORY_FORCE_MIXED_MODE, 1);
        }
        return $this;
    }

    /**
     * Set category bottom CMS block.
     * @param string|null $blockId
     * @return $this
     */
    private function setBottomCmsBlock($blockId)
    {
        if ($blockId !== null) {
            $this->category->setData('bottom_cms_block', $blockId);
            $this->category->setData(CategoryManager::CATEGORY_FORCE_MIXED_MODE, 1);
        }

        return $this;
    }

    /**
     * Set category display mode.
     */
    private function setDisplayMode(): Brand
    {
        if ($this->category->getDisplayMode() === Category::DM_PAGE) {
            $this->category->setData(self::CATEGORY_DISPLAY_MODE_KEY, Category::DM_PRODUCT);
        }

        return $this;
    }
}
