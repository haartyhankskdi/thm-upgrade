<?php
namespace Haartyhanks\RemoveObjectManager\Block;

use Mageplaza\Blog\Model\CategoryFactory;
use Mageplaza\Blog\Block\Listpost;
use Magento\Framework\View\Element\Template;

class BlogPosts extends Template
{
    protected $categoryFactory;
    protected $listPost;

    public function __construct(
        Template\Context $context,
        CategoryFactory $categoryFactory,
        Listpost $listPost,
        array $data = []
    ) {
        $this->categoryFactory = $categoryFactory;
        $this->listPost = $listPost;
        parent::__construct($context, $data);
    }

    public function getFilteredPostsByMagentoCategory($magentoCategoryName, $limit = 2)
    {
        $filteredPosts = [];

        if ($magentoCategoryName) {
            $postCollection = $this->listPost->getPostCollection();
            $postCollection->addFieldToFilter('enabled', 1);
            $postCollection->setOrder('publish_date', 'DESC');

            foreach ($postCollection as $post) {
                $categoryNames = [];

                foreach ($post->getCategoryIds() as $catId) {
                    $cat = $this->categoryFactory->create()->load($catId);
                    if ($cat->getId()) {
                        $categoryNames[] = $cat->getName();
                    }
                }

                if (in_array($magentoCategoryName, $categoryNames)) {
                    $filteredPosts[] = $post;
                }

                if (count($filteredPosts) >= $limit) {
                    break;
                }
            }
        }

        return $filteredPosts;
    }
}
