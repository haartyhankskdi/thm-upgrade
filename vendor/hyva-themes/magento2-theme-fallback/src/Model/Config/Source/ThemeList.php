<?php
/**
 * Hyvä Themes - https://hyva.io
 * Copyright © Hyvä Themes 2020-present. All rights reserved.
 * This product is licensed per Magento install
 * See https://hyva.io/license
 */
declare(strict_types=1);

namespace Hyva\ThemeFallback\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Theme\Model\ResourceModel\Theme\Collection as ThemeCollection;
use Magento\Theme\Model\ResourceModel\Theme\CollectionFactory as ThemeCollectionFactory;

class ThemeList implements OptionSourceInterface
{
    /** @var ThemeCollectionFactory */
    private $themeCollectionFactory;

    public function __construct(
        ThemeCollectionFactory $themeCollectionFactory
    ) {
        $this->themeCollectionFactory = $themeCollectionFactory;
    }

    public function toOptionArray(): array
    {
        /** @var ThemeCollection $themeCollection */
        $themeCollection = $this->themeCollectionFactory->create();
        $themeCollection->addAreaFilter();

        $themes = [];

        foreach ($themeCollection->getItems() as $theme) {
            $themes[] = [
                'value' => "{$theme->getArea()}/{$theme->getThemePath()}",
                'label' => $theme->getThemeTitle(),
            ];
        }

        return $themes;
    }
}
