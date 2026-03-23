<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Model\Consent;

use Amasty\Gdpr\Model\Consent\DataProvider\FrontendData;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;

class Validator
{
    /**
     * @var FrontendData
     */
    private $frontendData;

    public function __construct(FrontendData $frontendData)
    {
        $this->frontendData = $frontendData;
    }

    public function validate(string $location, array $consentData)
    {
        $consentCollection = $this->frontendData->getData($location);

        foreach ($consentCollection as $consent) {
            if ($consent->getIsRequired()
                && (!$consentData
                    || !array_key_exists($consent->getConsentCode(), $consentData)
                    || (array_key_exists($consent->getConsentCode(), $consentData)
                        && $consentData[$consent->getConsentCode()] == 0))
            ) {
                return false;
            }
        }

        return true;
    }
}
