<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model;

use Amasty\Gdpr\Model\Consent\Consent;
use Amasty\Gdpr\Model\Consent\ConsentStore\ConsentStore;
use Amasty\Gdpr\Model\Consent\ResourceModel\Collection;
use Amasty\Gdpr\Model\Consent\ResourceModel\CollectionFactory;
use Amasty\Gdpr\Model\Source\ConsentLinkType;
use Magento\Store\Model\Store;

class ConsentProvider
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var array
     */
    private $cacheConsents = [];

    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    public function getConsentsByStore(int $storeId = Store::DEFAULT_STORE_ID): Collection
    {
        if (array_key_exists($storeId, $this->cacheConsents)) {
            return $this->cacheConsents[$storeId];
        }

        $consent = $this->collectionFactory
            ->create()
            ->addStoreData($storeId)
            ->addFieldToFilter(ConsentStore::LINK_TYPE, ConsentLinkType::PRIVACY_POLICY)
            ->addFieldToFilter(ConsentStore::IS_ENABLED, true)
            ->addOrder(Consent::CONSENT_CODE, Collection::SORT_ORDER_ASC);

        return $this->cacheConsents[$storeId] = $consent;
    }

    public function _resetState(): void
    {
        $this->cacheConsents = [];
    }
}
