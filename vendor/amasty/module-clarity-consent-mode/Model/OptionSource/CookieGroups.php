<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Cookie Clarity Consent Mode
 */

namespace Amasty\ClarityConsentMode\Model\OptionSource;

use Amasty\GdprCookie\Model\CookieGroup;
use Amasty\GdprCookie\Model\ResourceModel\CookieGroup\Collection;
use Magento\Framework\Data\OptionSourceInterface;

class CookieGroups implements OptionSourceInterface
{
    public function __construct(
        private readonly Collection $cookieGroupsCollection
    ) {
    }

    public function toOptionArray(): array
    {
        $result = [['value' => 0, 'label' => __('Please Select..')]];
        foreach ($this->toArray() as $value => $label) {
            $result[] = ['value' => $value, 'label' => $label];
        }

        return $result;
    }

    public function toArray(): array
    {
        return $this->getCookieGroups();
    }

    private function getCookieGroups(): array
    {
        $cookieGroups = [];

        /** @var CookieGroup $cookieGroup */
        foreach ($this->cookieGroupsCollection->getItems() as $cookieGroup) {
            $cookieGroups[$cookieGroup->getId()] = $cookieGroup->getName();
        }

        return $cookieGroups;
    }
}
