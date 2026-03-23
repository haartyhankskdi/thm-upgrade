<?php
namespace Haartyhanks\AutoUpsell\Block\Product\View;

use Magento\Framework\View\Element\Template;
use Magento\Review\Model\ReviewFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Registry;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;

class ReviewData extends Template
{
    protected $reviewFactory;
    protected $storeManager;
    protected $registry;
    protected $productRepository;

    public function __construct(
        Template\Context $context,
        ReviewFactory $reviewFactory,
        StoreManagerInterface $storeManager,
        Registry $registry,
        ProductRepositoryInterface $productRepository,
        array $data = []
    ) {
        $this->reviewFactory = $reviewFactory;
        $this->storeManager = $storeManager;
        $this->registry = $registry;
        $this->productRepository = $productRepository;
        parent::__construct($context, $data);
    }

    /**
     * Get current product using registry or product_id from block data
     *
     * @return Product|null
     */
    public function getCurrentProduct()
    {
        $product = $this->registry->registry('product');

        if (!$product && $this->hasData('product_id')) {
            try {
                $productId = $this->getData('product_id');
                $product = $this->productRepository->getById($productId);
                $this->registry->register('product', $product);
            } catch (\Exception $e) {
                return null;
            }
        }

        return $product;
    }

    /**
     * Wrapper for current product
     */
    public function getProduct()
    {
        return $this->getCurrentProduct();
    }

    /**
     * Get review collection for current product
     *
     * @return \Magento\Review\Model\ResourceModel\Review\Collection|array
     */
    public function getReviews()
    {
        $product = $this->getProduct();
        if (!$product || !$product->getId()) {
            return [];
        }

        $storeId = $this->storeManager->getStore()->getId();

        $reviewModel = $this->reviewFactory->create();
        return $reviewModel->getResourceCollection()
            ->addEntityFilter('product', $product->getId())
            ->addStatusFilter(\Magento\Review\Model\Review::STATUS_APPROVED)
            ->setDateOrder()
            ->addRateVotes()
            ->addStoreFilter($storeId);
    }

    /**
     * Calculate review summary (average rating, stars, etc.)
     *
     * @return array
     */
    public function getReviewData()
    {
        $reviews = $this->getReviews();

        $totalReviews = count($reviews);
        $totalRating = 0;
        $ratingCount = 0;

        foreach ($reviews as $review) {
            foreach ($review->getRatingVotes() as $vote) {
                $totalRating += $vote->getPercent();
                $ratingCount++;
            }
        }

        $average = $ratingCount > 0 ? $totalRating / $ratingCount : 0;
        $averageStars = round($average / 20, 1); // convert to 0–5 scale
        $fullStars = floor($averageStars);
        $halfStar = ($averageStars - $fullStars) >= 0.5;
        $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);

        return [
            'total_reviews' => $totalReviews,
            'average_stars' => $averageStars,
            'full_stars' => $fullStars,
            'half_star' => $halfStar,
            'empty_stars' => $emptyStars
        ];
    }
}
