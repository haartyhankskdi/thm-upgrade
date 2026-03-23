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
interface ActionLogRepositoryInterface
{
    /**
     * Save
     *
     * @param \Amasty\Gdpr\Api\Data\ActionLogInterface $actionLog
     * @return \Amasty\Gdpr\Api\Data\ActionLogInterface
     */
    public function save(\Amasty\Gdpr\Api\Data\ActionLogInterface $actionLog);

    /**
     * Get by id
     *
     * @param int $id
     * @return \Amasty\Gdpr\Api\Data\ActionLogInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id);

    /**
     * Delete
     *
     * @param \Amasty\Gdpr\Api\Data\ActionLogInterface $actionLog
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\Gdpr\Api\Data\ActionLogInterface $actionLog);

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
