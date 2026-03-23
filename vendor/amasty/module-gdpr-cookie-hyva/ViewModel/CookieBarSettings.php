<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Cookie Hyva Compatibility (System)
 */

namespace Amasty\GdprCookieHyva\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;

class CookieBarSettings implements ArgumentInterface
{
    public const REF_NAME = 'am-cookie-bar-settings';
    public const OPEN_ACTION = 'open-am-cookie-bar-settings';
    public const CLOSE_ACTION = 'close-am-cookie-bar-settings';
}
