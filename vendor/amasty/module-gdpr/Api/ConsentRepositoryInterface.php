<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Api;

use Amasty\Gdpr\Api\Data\ConsentInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;

interface ConsentRepositoryInterface
{
    /**
     * @param int $consentId
     * @param int $storeId
     *
     * @return ConsentInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $consentId, int $storeId = Store::DEFAULT_STORE_ID);

    /**
     * @param int $consentId
     *
     * @return bool true on success
     */
    public function deleteById(int $consentId);

    /**
     * @param ConsentInterface $consent
     *
     * @return bool
     */
    public function delete(ConsentInterface $consent);

    /**
     * @param ConsentInterface $consent
     */
    public function save(ConsentInterface $consent);

    /**
     * @param string $consentCode
     * @param int $storeId
     *
     * @return ConsentInterface
     */
    public function getByConsentCode(string $consentCode, int $storeId = Store::DEFAULT_STORE_ID);
}
