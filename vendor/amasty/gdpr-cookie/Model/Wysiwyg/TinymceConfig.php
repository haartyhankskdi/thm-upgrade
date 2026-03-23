<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Model\Wysiwyg;

use Magento\Framework\DataObject;
use Magento\Cms\Model\Wysiwyg\DefaultConfigProvider;

/**
 * Modify tinymce toolbar in wysiwyg config. Image, table, charmap element are deleted.
 *
 * @see \Magento\Cms\Model\Wysiwyg\CompositeConfigProvider
 */
class TinymceConfig extends DefaultConfigProvider
{
    public function getConfig(DataObject $config): DataObject
    {
        $parentConfig = parent::getConfig($config);
        $tinymce = $parentConfig->getData('tinymce');
        $tinymce['toolbar'] = 'undo redo | styleselect | fontsizeselect | lineheight | forecolor backcolor | bold '
                            . 'italic underline | alignleft aligncenter alignright | numlist bullist | link';
        $config->setData('tinymce', $tinymce);

        return $config;
    }
}
