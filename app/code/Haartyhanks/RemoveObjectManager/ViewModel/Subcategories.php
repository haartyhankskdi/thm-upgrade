<?php
namespace Haartyhanks\RemoveObjectManager\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\Registry;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryRepository;

class Subcategories implements ArgumentInterface
{
    protected $categoryRepository;
    protected $registry;

    public function __construct(
        Registry $registry,
        CategoryRepository $categoryRepository
    ) {
        $this->registry = $registry;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Get current category from registry
     */
    public function getCurrentCategory(): ?Category
    {
        return $this->registry->registry('current_category');
    }

    /**
     * Get HTML for featured subcategories recursively
     */
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
                $html .= '<a href="' . $fullCategory->getUrl() . '" class="inline-flex bg-black text-white items-center justify-center font-bold  w-[150px] h-[80px] text-[18px]  md:w-[183px] md:h-[98px] md:text-[25px] hover:bg-[#E63833] transition-colors duration-300">';
                $html .= '<div class="category-box">';
                $html .= htmlspecialchars($fullCategory->getName(), ENT_QUOTES, 'UTF-8');
                $html .= '</div>';
                $html .= '</a>';
            }

            // Recursive call
            $html .= $this->getSubcategoryHtml($child);
        }

        return $html;
    }
}
