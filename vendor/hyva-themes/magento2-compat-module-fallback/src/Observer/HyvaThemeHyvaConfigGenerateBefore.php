<?php
/**
 * Hyvä Themes - https://hyva.io
 * Copyright © Hyvä Themes 2020-present. All rights reserved.
 * This category is licensed per Magento install
 * See https://hyva.io/license
 */

declare(strict_types=1);

namespace Hyva\CompatModuleFallback\Observer;

use Hyva\CompatModuleFallback\Model\CompatModuleRegistry;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Event\Observer as Event;
use Magento\Framework\Event\ObserverInterface;
use function array_map as map;
use function array_filter as filter;

class HyvaThemeHyvaConfigGenerateBefore implements ObserverInterface
{
    /** @var CompatModuleRegistry $compatModuleRegistry */
    protected $compatModuleRegistry;

    /** @var ComponentRegistrarInterface $componentRegistrar */
    protected $componentRegistrar;

    /** @var @array */
    protected $exclusions;

    public function __construct(
        CompatModuleRegistry $compatModuleRegistry,
        ComponentRegistrarInterface $componentRegistrar,
        array $exclusions = []
    ) {
        $this->compatModuleRegistry = $compatModuleRegistry;
        $this->componentRegistrar = $componentRegistrar;
        $this->exclusions = $exclusions;
    }

    /**
     * List registered compatibility module paths as specified in the modules "registration.php"
     *
     * The modules will be added to the app/etc/hyva-themes.json by "bin/magento hyva:config:generate".
     *
     * The array structure set on the event "config" object is
     * [
     *   "extensions": [
     *     ["src" => "vendor/vendor-name/magento2-module-name/src"],
     *     ...
     *   ]
     * ]
     *
     * Module's added to this class's "exclusions" array via DI will be omitted,
     * preventing the loading of their Tailwind configurations.
     *
     * @param Event $event
     */
    public function execute(Event $event)
    {
        $config = $event->getConfig();

        $paths = filter(map(function ($module) {
            if (isset($this->exclusions[$module]) && $this->exclusions[$module]) {
                return null; // skip modules configured to be excluded
            }

            $path = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, $module);

            if ($path && substr($path, 0, strlen(BP)) === BP) {
                return ['src' => substr($path, strlen(BP) + 1)];
            }

            return null;
        }, $this->compatModuleRegistry->getCompatModules()));

        $config->setData(
            'extensions',
            $config->hasData('extensions') ? array_merge_recursive($config->getData('extensions'), $paths) : $paths
        );
    }
}
