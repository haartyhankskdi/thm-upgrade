<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Api;

interface ReviewRepositoryInterface
{
    /**
     * Save
     *
     * @param \Amasty\Storelocator\Api\Data\ReviewInterface $review
     *
     * @return \Amasty\Storelocator\Api\ReviewRepositoryInterface
     */
    public function save(\Amasty\Storelocator\Api\Data\ReviewInterface $review);

    /**
     * Get by id
     *
     * @param int $id
     *
     * @return \Amasty\Storelocator\Api\Data\ReviewInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id);

    /**
     * Delete
     *
     * @param \Amasty\Storelocator\Api\Data\ReviewInterface $review
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\Storelocator\Api\Data\ReviewInterface $review);

    /**
     * Delete by id
     *
     * @param int $id
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function deleteById($id);

    /**
     * @param array $locationIds
     */
    public function loadReviewForLocations(array $locationIds): void;

    /**
     * @param array $locationIds
     *
     * @return \Amasty\Storelocator\Api\Data\ReviewInterface[]
     */
    public function getLocationReviews(array $locationIds): array;

    /**
     * @param int $locationId
     *
     * @return \Amasty\Storelocator\Api\Data\ReviewInterface[]
     */
    public function getApprovedByLocationId($locationId);
}
