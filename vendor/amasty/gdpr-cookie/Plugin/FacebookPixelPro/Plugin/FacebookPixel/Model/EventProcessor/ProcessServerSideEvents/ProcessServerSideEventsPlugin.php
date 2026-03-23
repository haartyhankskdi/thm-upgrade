<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Plugin\FacebookPixelPro\Plugin\FacebookPixel\Model\EventProcessor\ProcessServerSideEvents;

use Amasty\FacebookPixelPro\Plugin\FacebookPixel\Model\EventProcessor\ProcessServerSideEvents;
use Amasty\GdprCookie\Model\FacebookPixelPro\IsFbCookieAllowed;

class ProcessServerSideEventsPlugin
{
    /**
     * @var IsFbCookieAllowed
     */
    private IsFbCookieAllowed $isFbCookieAllowed;

    public function __construct(
        IsFbCookieAllowed $isFbCookieAllowed
    ) {
        $this->isFbCookieAllowed = $isFbCookieAllowed;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterIsAllowedToProcessServerEvent(ProcessServerSideEvents $subject, bool $result): bool
    {
        if (!$this->isFbCookieAllowed->execute()) {
            return false;
        }

        return $result;
    }
}
