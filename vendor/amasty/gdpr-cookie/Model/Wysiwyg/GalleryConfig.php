<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Model\Wysiwyg;

use Magento\Framework\Data\Wysiwyg\ConfigProviderInterface;
use Magento\Framework\DataObject;

/**
 * Skip default gallery config processors to prevent adding 'Insert Image...' button in Cookie and CookieGroup forms.
 *
 * @see \Magento\Cms\Model\Wysiwyg\CompositeConfigProvider
 */
class GalleryConfig implements ConfigProviderInterface
{
    public function getConfig(DataObject $config): DataObject
    {
        return $config;
    }
}
