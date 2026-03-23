<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\Policy\Validator;

use Amasty\Gdpr\Model\Config;
use Amasty\Gdpr\Model\CountryRestrictions\Validator;

class Displaying
{
    /**
     * @var Config
     */
    private $configProvider;

    /**
     * @var Validator
     */
    private $validator;

    public function __construct(
        Validator $validator,
        Config $configProvider
    ) {
        $this->validator = $validator;
        $this->configProvider = $configProvider;
    }

    public function isDisplay(?string $store = null, ?string $customerIp = null): bool
    {
        return $this->configProvider->isModuleEnabled($store)
            && $this->configProvider->isDisplayPpPopup($store)
            && $this->validator->isCountryAllowed($store, $customerIp);
    }
}
