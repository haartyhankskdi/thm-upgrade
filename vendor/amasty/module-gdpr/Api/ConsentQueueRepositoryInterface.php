<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Api;

use Amasty\Gdpr\Api\Data\ConsentQueueInterface;
use Magento\Framework\Api\SearchResultsInterface;

/**
 * @api
 */
interface ConsentQueueRepositoryInterface
{
    /**
     * Save
     *
     * @param \Amasty\Gdpr\Api\Data\ConsentQueueInterface $deleteRequest
     * @return \Amasty\Gdpr\Api\Data\ConsentQueueInterface
     */
    public function save(\Amasty\Gdpr\Api\Data\ConsentQueueInterface $deleteRequest): ConsentQueueInterface;

    /**
     * Get by id
     *
     * @param int $id
     * @return \Amasty\Gdpr\Api\Data\ConsentQueueInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById(int $id): ConsentQueueInterface;

    /**
     * Delete
     *
     * @param \Amasty\Gdpr\Api\Data\ConsentQueueInterface $deleteRequest
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\Gdpr\Api\Data\ConsentQueueInterface $deleteRequest): bool;

    /**
     * Delete by id
     *
     * @param int $id
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById(int $id): bool;

    /**
     * Lists
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Framework\Api\SearchResultsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria): SearchResultsInterface;
}
