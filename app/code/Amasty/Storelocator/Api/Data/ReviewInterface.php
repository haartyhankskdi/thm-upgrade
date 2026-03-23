<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Api\Data;

interface ReviewInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    public const ID = 'id';

    public const LOCATION_ID = 'location_id';

    public const CUSTOMER_ID = 'customer_id';

    public const REVIEW_TEXT = 'review_text';

    public const RATING = 'rating';

    public const PLACED_AT = 'placed_at';

    public const PUBLISHED_AT = 'published_at';

    public const STATUS = 'status';

    /**#@-*/

    /**
     * @return int
     */
    public function getLocationId();

    /**
     * @param int $id
     *
     * @return \Amasty\Storelocator\Api\Data\ReviewInterface
     */
    public function setLocationId($id);

    /**
     * @return int
     */
    public function getCustomerId();

    /**
     * @param int $id
     *
     * @return \Amasty\Storelocator\Api\Data\ReviewInterface
     */
    public function setCustomerId($id);

    /**
     * @return string
     */
    public function getRating();

    /**
     * @param string $rating
     *
     * @return \Amasty\Storelocator\Api\Data\ReviewInterface
     */
    public function setRating($rating);

    /**
     * @return string
     */
    public function getReviewText();

    /**
     * @param string $text
     *
     * @return \Amasty\Storelocator\Api\Data\ReviewInterface
     */
    public function setReviewText($text);

    /**
     * @return string
     */
    public function getPlacedAt();

    /**
     * @param string $date
     *
     * @return \Amasty\Storelocator\Api\Data\ReviewInterface
     */
    public function setPlacedAt($date);

    /**
     * @return string
     */
    public function getPublishedAt();

    /**
     * @param string $date
     *
     * @return \Amasty\Storelocator\Api\Data\ReviewInterface
     */
    public function setPublishedAt($date);

    /**
     * @return int
     */
    public function getStatus();

    /**
     * @param int $status
     *
     * @return \Amasty\Storelocator\Api\Data\ReviewInterface
     */
    public function setStatus($status);
}
