<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Model\Layout\Modal;

class CookieSettings extends LayoutProcessor
{
    protected function getSettings(): array
    {
        return array_filter([
            'backgroundColor' => $this->getConfigProvider()->getCookieSettingsBarBackgroundColor(),
            'groupTitleTextColor' => $this->getConfigProvider()->getCookieSettingsBarGroupTitleTextColor(),
            'groupDescriptionTextColor' => $this->getConfigProvider()->getCookieSettingsBarGroupDescriptionTextColor(),
            'groupLinksColor' => $this->getConfigProvider()->getCookieSettingsBarGroupLinksColor(),
            'doneButtonText' => $this->getConfigProvider()->getCookieSettingsBarDoneButtonText(),
            'doneButtonColor' => $this->getConfigProvider()->getCookieSettingsBarDoneButtonColor(),
            'doneButtonColorHover' => $this->getConfigProvider()->getCookieSettingsBarDoneButtonColorHover(),
            'doneButtonTextColor' => $this->getConfigProvider()->getCookieSettingsBarDoneButtonTextColor(),
            'doneButtonTextColorHover' => $this->getConfigProvider()->getCookieSettingsBarDoneButtonTextColorHover(),
            'toggleActiveColor' => $this->getConfigProvider()->getCookieSettingsBarToggleActiveStateColor(),
            'toggleInActiveColor' => $this->getConfigProvider()->getCookieSettingsBarToggleInActiveStateColor()
        ]);
    }
}
