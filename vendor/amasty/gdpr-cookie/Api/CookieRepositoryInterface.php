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
interface CookieRepositoryInterface
{
    /**
     * Save Cookie
     *
     * @param \Amasty\GdprCookie\Api\Data\CookieInterface $cookie
     * @param int $storeId
     *
     * @return \Amasty\GdprCookie\Api\Data\CookieInterface
     */
    public function save(\Amasty\GdprCookie\Api\Data\CookieInterface $cookie, int $storeId = 0);

    /**
     * Get cookie by id
     *
     * @param int $cookieId
     * @param int $storeId
     *
     * @return \Amasty\GdprCookie\Api\Data\CookieInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($cookieId, int $storeId = 0);

    /**
     * Get cookie by name
     *
     * @param string $cookieName
     *
     * @return \Amasty\GdprCookie\Api\Data\CookieInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByName($cookieName);

    /**
     * Delete Cookie
     *
     * @param \Amasty\GdprCookie\Api\Data\CookieInterface $cookie
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\GdprCookie\Api\Data\CookieInterface $cookie);

    /**
     * Delete cookie by id
     *
     * @param int $cookieId
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($cookieId);
}
