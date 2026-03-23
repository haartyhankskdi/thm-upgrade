<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Model\Layout\Modal;

class CookieInformation extends LayoutProcessor
{
    protected function getSettings(): array
    {
        return array_filter([
            'backgroundColor' => $this->getConfigProvider()->getCookieInformationBarBackgroundColor(),
            'titleColor' => $this->getConfigProvider()->getCookieInformationBarTitleColor(),
            'descriptionColor' => $this->getConfigProvider()->getCookieInformationBarDescriptionColor(),
            'tableHeaderColor' => $this->getConfigProvider()->getCookieInformationBarTableHeaderColor(),
            'tableContentColor' => $this->getConfigProvider()->getCookieInformationBarTableContentColor(),
            'doneButtonText' => $this->getConfigProvider()->getCookieInformationBarDoneButtonText(),
            'doneButtonColor' => $this->getConfigProvider()->getCookieInformationBarDoneButtonColor(),
            'doneButtonColorHover' => $this->getConfigProvider()->getCookieInformationBarDoneButtonColorHover(),
            'doneButtonTextColor' => $this->getConfigProvider()->getCookieInformationBarDoneButtonTextColor(),
            'doneButtonTextColorHover' => $this->getConfigProvider()->getCookieInformationBarDoneButtonTextColorHover()
        ]);
    }
}
