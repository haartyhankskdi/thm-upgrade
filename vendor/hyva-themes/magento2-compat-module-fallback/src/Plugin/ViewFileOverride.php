<?php declare(strict_types=1);
/**
 * Hyvä Themes - https://hyva.io
 * Copyright © Hyvä Themes 2021-present. All rights reserved.
 * This product is licensed per Magento install
 * See https://hyva.io/license
 */

namespace Hyva\CompatModuleFallback\Plugin;

use Hyva\CompatModuleFallback\Model\CompatModuleRegistry;
use Hyva\Theme\Service\CurrentTheme;
use Magento\Framework\Module\Dir as ModuleDir;
use Magento\Framework\View\Design\Fallback\Rule\ModularSwitch;

use function array_filter as filter;
use function array_map as map;
use function array_merge as merge;
use function array_reduce as reduce;

class ViewFileOverride
{

    /**
     * @var CurrentTheme
     */
    private $currentTheme;

    /**
     * @var CompatModuleRegistry
     */
    private $compatModuleRegistry;

    /**
     * @var ModuleDir
     */
    private $moduleDir;

    public function __construct(
        CurrentTheme $currentTheme,
        ModuleDir $moduleDir,
        CompatModuleRegistry $compatModuleRegistry
    ) {
        $this->currentTheme         = $currentTheme;
        $this->moduleDir            = $moduleDir;
        $this->compatModuleRegistry = $compatModuleRegistry;
    }

    public function afterGetPatternDirs(ModularSwitch $subject, array $fallbackDirsResult, array $params)
    {
        if (
            isset($params['module_name']) &&
            $this->currentTheme->isHyva() &&
            ($compatModules = $this->compatModuleRegistry->getCompatModulesFor($params['module_name']))
        ) {
            return $this->injectCompatModulesDirs($params['module_name'], $compatModules, $fallbackDirsResult);
        }
        return $fallbackDirsResult;
    }

    /**
     * Inject fallback module paths before every existing fallback path of the original module
     *
     * @param string $origModule
     * @param array $compatModules
     * @param array $fallbackDirsResult
     * @return array
     */
    private function injectCompatModulesDirs(string $origModule, array $compatModules, array $fallbackDirsResult): array
    {
        $origModuleDir    = $this->moduleDir->getDir($origModule);
        $compatModuleDirs = filter(map(function (string $compatModule): ?string {
            return $this->moduleDir->getDir($compatModule);
        }, $compatModules));

        $dirs = map(function (string $origFallbackDir) use ($origModule, $origModuleDir, $compatModuleDirs): array {
            return strpos($origFallbackDir, $origModuleDir) === 0
                ? $this->injectCompatModuleFallbackDirs(
                    $origModule,
                    $origModuleDir,
                    $origFallbackDir,
                    $compatModuleDirs
                )
                : [$origFallbackDir];
        }, $fallbackDirsResult);

        return merge(...$dirs);
    }

    private function injectCompatModuleFallbackDirs(
        string $origModule,
        ?string $origModuleDir,
        string $origFallbackDir,
        array $compatModuleDirs
    ): array {
        $pathInModule = substr($origFallbackDir, strlen($origModuleDir));
        return merge(
            $this->getCompatModuleDirs($origModule, $compatModuleDirs, $pathInModule),
            [$origFallbackDir]
        );
    }

    private function getCompatModuleDirs(string $origModule, array $compatModuleDirs, string $pathInModule): array
    {
        return merge(...reduce(
            $compatModuleDirs,
            function (array $acc, string $compatModuleDir) use ($origModule, $pathInModule) {
                $acc[] = [
                    // Compat_Module/view/frontend/templates/Orig_Module
                    $compatModuleDir . $pathInModule . '/' . $origModule,
                    // Compat_Module/view/frontend/templates
                    $compatModuleDir . $pathInModule,
                ];
                return $acc;
            },
            []
        ));
    }

}
