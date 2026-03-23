<?php declare(strict_types=1);
/**
 * Hyvä Themes - https://hyva.io
 * Copyright © Hyvä Themes 2021-present. All rights reserved.
 * This product is licensed per Magento install
 * See https://hyva.io/license
 */

namespace Hyva\CompatModuleFallback\Model;

use function array_keys as keys;
use function array_map as map;
use function array_values as values;
use function array_unique as unique;

class CompatModuleRegistry
{
    /**
     * @var array[]
     */
    private $compatModules = [];

    /**
     * Register Hyvä compatibility modules for automatic view file overrides.
     *
     * Since modules can have more than one compatibility module, and compatibility modules can be applicable to more
     * than one original module, the constructor argument map can not be a simple associative array.
     *
     * The expected constructor argument array structure is this:
     * [
     *     ['original_module' => 'Original_Module', 'compat_module' => 'Hyva_OriginalModule'],
     * ]
     *
     * In di.xml, compatibility modules register themselves like this:
     *
     * <type name="Hyva\CompatModuleFallback\Model\CompatModuleRegistry">
     *     <arguments>
     *         <argument name="compatModules" xsi:type="array">
     *             <item name="hyva_origmodule_map" xsi:type="array">
     *                 <item name="original_module" xsi:type="string">Orig_Module</item>
     *                 <item name="compat_module" xsi:type="string">Hyva_OrigModule</item>
     *             </item>
     *             <item name="hyva_othermodule_map" xsi:type="array">
     *                 <item name="original_module" xsi:type="string">Other_Module</item>
     *                 <item name="compat_module" xsi:type="string">Hyva_OtherModule</item>
     *             </item>
     *         </argument>
     *     </arguments>
     * </type>
     *
     * Note: the keys of the first level array do not matter, as long as they are unique.
     *
     * @param array[] $compatModules
     */
    public function __construct(array $compatModules = [])
    {
        map(function (array $compatModule): void {
            $this->register($compatModule['original_module'], $compatModule['compat_module']);
        }, $compatModules);
    }

    public function register(string $originalModule, string $compatModule): void
    {
        $this->compatModules[$originalModule][] = $compatModule;
    }

    /**
     * Return array of registered compatibility modules for the given Magento module name.
     *
     * @param string $originalModule
     * @return string[]
     */
    public function getCompatModulesFor(string $originalModule): array
    {
        return $this->compatModules[$originalModule] ?? [];
    }

    /**
     * @return string[]
     */
    public function getCompatModules(): array
    {
        $compatModules = $this->compatModules
            ? values($this->compatModules)
            : [[]];
        return unique(call_user_func_array('array_merge', $compatModules));
    }

    /**
     * @return string[]
     */
    public function getOrigModules(): array
    {
        return keys($this->compatModules);
    }

    /**
     * Used for testing
     */
    public function clear(): void
    {
        $this->compatModules = [];
    }
}
