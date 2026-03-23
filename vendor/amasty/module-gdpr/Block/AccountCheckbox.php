<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Block;

use Amasty\Gdpr\Model\Consent;

/**
 * Checkbox for customer account privacy settings page
 */
class AccountCheckbox extends Checkbox
{
    public function isChecked(Consent\Consent $consent): bool
    {
        return $this->dataProvider->haveAgreement($consent);
    }

    public function isRequired(Consent\Consent $consent): bool
    {
        return false;
    }
}
