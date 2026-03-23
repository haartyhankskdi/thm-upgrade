<?php
namespace Haartyhanks\BannerApi\Model;

use Haartyhanks\BannerApi\Api\BannerInterface;
use Magento\Cms\Model\ResourceModel\Block\CollectionFactory as BlockCollectionFactory;

class Banner implements BannerInterface
{
    protected $blockCollectionFactory;

    public function __construct(BlockCollectionFactory $blockCollectionFactory)
    {
        $this->blockCollectionFactory = $blockCollectionFactory;
    }

    public function getBanners()
    {
        $identifier = 'new-banner-api';

        $collection = $this->blockCollectionFactory->create()
            ->addFieldToFilter('identifier', $identifier)
            ->setPageSize(1);

        $block = $collection->getFirstItem();

        if (!$block || !$block->getId()) {
            return ['error' => 'Data block not found'];
        }

        $content = html_entity_decode(strip_tags($block->getContent()));

        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['error' => 'Invalid JSON Data', 'raw' => $content];
        }

        return $data;
    }
}
