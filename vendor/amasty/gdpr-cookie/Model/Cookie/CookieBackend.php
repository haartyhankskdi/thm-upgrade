<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Model\Cookie;

class CookieBackend extends CookieManagement
{
    protected function createCookieCollection(int $storeId = 0)
    {
        $collection = $this->cookieCollectionFactory->create();
        $collection->setStoreId($storeId);

        return $collection;
    }
}
