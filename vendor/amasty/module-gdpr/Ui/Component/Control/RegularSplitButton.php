<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Ui\Component\Control;

class RegularSplitButton extends \Magento\Ui\Component\Control\SplitButton
{
    public function getAttributesHtml()
    {
        $classes = ['amgdpr-regular-split-button'];

        if (!($title = $this->getTitle())) {
            $title = $this->getLabel();
        }

        if ($this->hasSplit()) {
            $classes[] = 'actions-split';
        }

        return $this->attributesToHtml(['title' => $title, 'class' => implode(' ', $classes)]);
    }

    protected function attributesToHtml($attributes)
    {
        $classes = explode(' ', $attributes['class'] ?? []);

        foreach ($classes as $classIndex => $class) {
            if (in_array($class, ['action-default', 'primary'])) {
                unset($classes[$classIndex]);
            }
        }

        $attributes['class'] = implode(' ', $classes);

        return parent::attributesToHtml($attributes);
    }
}
