<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\Consent\DataProvider;

use Amasty\Gdpr\Model\Consent\ConsentStore\ConsentStore;
use Amasty\Gdpr\Model\Consent\ResourceModel\Collection;
use Amasty\Gdpr\Model\ConsentLogger;

class PrivacySettingsDataProvider extends FrontendData
{
    public function getData(string $location)
    {
        if ($location !== ConsentLogger::FROM_PRIVACY_SETTINGS) {
            return [];
        }
        $collection = $this->collectionFactory->create();
        $storeId = (int)$this->storeManager->getStore()->getId();
        $collection->addStoreData($storeId)
            ->addFieldToFilter(ConsentStore::IS_ENABLED, true)
            ->addFieldToFilter(ConsentStore::HIDE_CONSENT_AFTER_USER_LEFT_THE_CONSENT, true)
            ->addOrder(ConsentStore::SORT_ORDER, Collection::SORT_ORDER_ASC);

        return array_filter($collection->getItems(), function ($consent) {
            return $this->isNeedShowConsentByCountry($consent) && $consent->isLogTheConsent();
        });
    }
}
