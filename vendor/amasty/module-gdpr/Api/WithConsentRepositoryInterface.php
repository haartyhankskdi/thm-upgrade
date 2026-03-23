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
interface WithConsentRepositoryInterface
{
    /**
     * Save
     *
     * @param \Amasty\Gdpr\Api\Data\WithConsentInterface $withConsent
     * @return \Amasty\Gdpr\Api\Data\WithConsentInterface
     */
    public function save(\Amasty\Gdpr\Api\Data\WithConsentInterface $withConsent);

    /**
     * Get by id
     *
     * @param int $id
     * @return \Amasty\Gdpr\Api\Data\WithConsentInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id);

    /**
     * Delete
     *
     * @param \Amasty\Gdpr\Api\Data\WithConsentInterface $withConsent
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\Gdpr\Api\Data\WithConsentInterface $withConsent);

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
     * @return \Amasty\Gdpr\Api\Data\WithConsentSearchResultsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
