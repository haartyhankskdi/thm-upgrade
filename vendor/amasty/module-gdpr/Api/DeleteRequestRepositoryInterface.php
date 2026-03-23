<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Api;

/**
 * @api
 */
interface DeleteRequestRepositoryInterface
{
    /**
     * Save
     *
     * @param \Amasty\Gdpr\Api\Data\DeleteRequestInterface $deleteRequest
     * @return \Amasty\Gdpr\Api\Data\DeleteRequestInterface
     */
    public function save(\Amasty\Gdpr\Api\Data\DeleteRequestInterface $deleteRequest);

    /**
     * Get by id
     *
     * @param int $id
     * @return \Amasty\Gdpr\Api\Data\DeleteRequestInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id);

    /**
     * Delete
     *
     * @param \Amasty\Gdpr\Api\Data\DeleteRequestInterface $deleteRequest
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\Gdpr\Api\Data\DeleteRequestInterface $deleteRequest);

    /**
     * Delete by id
     *
     * @param int $id
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($id);

    /**
     * Lists
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Framework\Api\SearchResultsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
