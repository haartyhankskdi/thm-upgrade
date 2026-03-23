<?php
namespace Haartyhanks\RemoveObjectManager\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Registry;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryRepository;

class Subcategories extends Template
{
        protected $categoryRepository;
    protected $registry;

    public function __construct(
        Template\Context $context,
        Registry $registry,
        CategoryRepository $categoryRepository,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->categoryRepository = $categoryRepository;
        parent::__construct($context, $data);
    }

    public function getCurrentCategory(): ?Category
    {
        return $this->registry->registry('current_category');
    }

    public function getSubcategoryHtml(Category $category): string
{
    $html = '';
        $children = $category->getChildrenCategories()
        ->addAttributeToSelect(['name', 'url_key', 'featured_cat'])
        ->addAttributeToFilter('is_active', 1);

    foreach ($children as $child) {
        $fullCategory = $this->categoryRepository->get($child->getId());
        $fullCategory->load($child->getId());

        if ($fullCategory->getData('featured_cat') == 1) {
            $html .= '<a href="' . $fullCategory->getUrl() . '">';
            $html .= '<div class="category-box">';
            $html .= $this->escapeHtml($fullCategory->getName());
            $html .= '</div>';
            $html .= '</a>';
        }

        // Recursive call
        $html .= $this->getSubcategoryHtml($child);
    }

    return $html;
}

}
