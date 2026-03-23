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
interface PolicyRepositoryInterface
{
    /**
     * Save
     *
     * @param \Amasty\Gdpr\Api\Data\PolicyInterface $policy
     * @return \Amasty\Gdpr\Api\Data\PolicyInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Amasty\Gdpr\Api\Data\PolicyInterface $policy);

    /**
     * Get by id
     *
     * @param int $id
     * @return \Amasty\Gdpr\Api\Data\PolicyInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id);

    /**
     * Delete
     *
     * @param \Amasty\Gdpr\Api\Data\PolicyInterface $policy
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\Gdpr\Api\Data\PolicyInterface $policy);

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

    /**
     * Get the latest version of consent
     * false value is returned if no enabled consent found
     *
     * @param int|bool $storeId
     *
     * @return \Amasty\Gdpr\Api\Data\PolicyInterface|false
     */
    public function getCurrentPolicy($storeId = false);
}
