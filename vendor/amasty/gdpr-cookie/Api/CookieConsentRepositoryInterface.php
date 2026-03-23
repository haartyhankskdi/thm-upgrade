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
interface CookieConsentRepositoryInterface
{
    /**
     * Save
     *
     * @param \Amasty\GdprCookie\Api\Data\CookieConsentInterface $cookieConsent
     *
     * @return \Amasty\GdprCookie\Api\Data\CookieConsentInterface
     */
    public function save(\Amasty\GdprCookie\Api\Data\CookieConsentInterface $cookieConsent);

    /**
     * Get by id
     *
     * @param int $id
     *
     * @return \Amasty\GdprCookie\Api\Data\CookieConsentInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id);

    /**
     * Delete
     *
     * @param \Amasty\GdprCookie\Api\Data\CookieConsentInterface $cookieConsent
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\GdprCookie\Api\Data\CookieConsentInterface $cookieConsent);

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
     * @param int $id
     *
     * @return \Amasty\GdprCookie\Api\Data\CookieConsentInterface|bool|array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByCustomerId($id);
}
