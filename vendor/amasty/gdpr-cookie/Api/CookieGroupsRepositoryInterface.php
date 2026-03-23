<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Api;

/**
 * @api
 */
interface CookieGroupsRepositoryInterface
{
    /**
     * Save Cookie Group
     *
     * @param \Amasty\GdprCookie\Api\Data\CookieGroupsInterface $group
     * @param int $storeId
     *
     * @return \Amasty\GdprCookie\Api\Data\CookieGroupsInterface
     */
    public function save(\Amasty\GdprCookie\Api\Data\CookieGroupsInterface $group, int $storeId = 0);

    /**
     * Get cookie group by id
     *
     * @param int $groupId
     * @param int $storeId
     *
     * @return \Amasty\GdprCookie\Api\Data\CookieGroupsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($groupId, int $storeId = 0);

    /**
     * Delete Cookie Group
     *
     * @param \Amasty\GdprCookie\Api\Data\CookieGroupsInterface $group
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\GdprCookie\Api\Data\CookieGroupsInterface $group);

    /**
     * Delete cookie group by id
     *
     * @param int $groupId
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($groupId);
}
