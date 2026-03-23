<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator Hyva Compatibility
 */

namespace Amasty\StorelocatorHyva\Plugin\Controller\Location;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Amasty\Storelocator\Api\ReviewRepositoryInterface;
use Amasty\Storelocator\Model\ReviewFactory;
use Amasty\Storelocator\Model\Review;
use Amasty\Storelocator\Model\Config\Source\ReviewStatuses;

class SaveReview
{
    /**
     * @var ReviewRepositoryInterface
     */
    private $reviewRepository;

    /**
     * @var ReviewFactory
     */
    private $reviewFactory;

    /**
     * @var Session
     */
    private $customerSession;

    public function __construct(
        ReviewRepositoryInterface $reviewRepository,
        ReviewFactory $reviewFactory,
        Session $customerSession
    ) {
        $this->reviewRepository = $reviewRepository;
        $this->reviewFactory = $reviewFactory;
        $this->customerSession = $customerSession;
    }

    public function aroundExecute(\Amasty\Storelocator\Controller\Location\SaveReview $subject, \Closure $proceed)
    {

        $customerId = $this->customerSession->getCustomerId();
        $data = $subject->getRequest()->getParams();

        if (isset($data['review-location-id']) && $customerId) {
            /** @var Review $review */
            $review = $this->reviewFactory->create();
            $review->setPlacedAt(time())
                ->setLocationId($data['review-location-id'])
                ->setRating($data['location-rating'] * Review::RATING_DIVIDER)
                ->setReviewText($data['detail'])
                ->setStatus(ReviewStatuses::PENDING)
                ->setCustomerId($customerId);
            $this->reviewRepository->save($review);
        }
    }
}
