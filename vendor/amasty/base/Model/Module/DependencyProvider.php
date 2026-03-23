<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Model\Module;

use Magento\Framework\Data\Graph;
use Magento\Framework\Module\ModuleList;
use Magento\Framework\Module\ModuleList\Loader;
use Magento\Framework\Module\PackageInfo;
use Magento\Framework\Module\PackageInfoFactory;

class DependencyProvider
{
    public const FLAG_AMASTY = 'amasty';
    public const FLAG_EXTERNAL = 'external';
    public const FLAG_ALL = 'all';
    public const FLAG_DEP = 'dep';

    /**
     * @var PackageInfo
     */
    private $packageInfo;

    /**
     * @var ModuleList
     */
    private $list;

    /**
     * @var Loader
     */
    private $loader;

    public function __construct(
        ModuleList $list,
        Loader $loader,
        PackageInfoFactory $packageInfoFactory
    ) {
        $this->list = $list;
        $this->loader = $loader;
        $this->packageInfo = $packageInfoFactory->create();
    }

    public function getDependencies(
        array $modules,
        string $flag,
        bool $isEnable,
        ?array $currentlyEnabledModules = null
    ): array {
        $masterList = $currentlyEnabledModules ?? $this->list->getNames();
        $enabledModules = $isEnable
            ? array_unique(array_merge($masterList, $modules))
            : array_diff($masterList, $modules);

        return $this->checkDependencyGraph($isEnable, $modules, $enabledModules, $flag);
    }

    private function checkDependencyGraph(
        bool $isEnable,
        array $moduleNames,
        array $enabledModules,
        string $flag
    ): array {
        $fullModuleList = $this->loader->load();
        $graph = $this->createGraph($fullModuleList);
        $allModules = array_merge(
            array_keys($fullModuleList),
            $this->packageInfo->getNonExistingDependencies()
        );
        $enabledModulesSet = array_flip($enabledModules);
        $allModulesSet = array_flip($allModules);

        $dependenciesMissingAll = $allDependenciesForDep = [];
        foreach ($moduleNames as $moduleName) {
            $paths = $graph->findPathsToReachableNodes($moduleName, Graph::INVERSE);
            $dependenciesMissing = [];
            foreach ($paths as $module => $path) {
                if (!isset($allModulesSet[$module])) {
                    continue;
                }
                $isModuleEnabled = isset($enabledModulesSet[$module]);
                if ($isEnable && !$isModuleEnabled) {
                    $dependenciesMissing[$module] = $path;
                } elseif (!$isEnable && $isModuleEnabled) {
                    $dependenciesMissing[$module] = array_reverse($path);
                }
                if ($flag !== self::FLAG_DEP
                    && !in_array($module, $dependenciesMissingAll, true)
                    && $this->filterModulesByFlag($flag, $module)
                ) {
                    $dependenciesMissingAll[] = $module;
                }
            }
            if ($flag === self::FLAG_DEP) {
                if (!empty($dependenciesMissing)) {
                    foreach ($dependenciesMissing as $path) {
                        $allDependenciesForDep[] = $path;
                    }
                } else {
                    $allDependenciesForDep[] = [$moduleName];
                }
            }
        }

        if ($flag === self::FLAG_DEP) {
            $mergedDeps = [];
            foreach ($allDependenciesForDep as $depGroup) {
                array_push($mergedDeps, ...$depGroup);
            }
            $dependenciesMissingAll = array_unique($mergedDeps);
        }

        return $dependenciesMissingAll;
    }

    private function filterModulesByFlag(string $flag, string $module): bool
    {
        switch ($flag) {
            case self::FLAG_AMASTY:
                return (bool)preg_match('/^Amasty_(?!Base$).+$/', $module);
            case self::FLAG_EXTERNAL:
                return !preg_match('/^(Magento_|Amasty_|PayPal_)/', $module);
            case self::FLAG_ALL:
                return !preg_match('/^(Amasty_Base|PayPal_[^\s]*|Magento_[^\s]*)$/', $module);
            default:
                return false;
        }
    }

    private function createGraph(array $fullModuleList): Graph
    {
        $nodes = $dependencies = [];
        foreach (array_keys($fullModuleList) as $moduleName) {
            $nodes[] = $moduleName;
            foreach ($this->packageInfo->getRequire($moduleName) as $dependModuleName) {
                if ($dependModuleName) {
                    $dependencies[] = [$moduleName, $dependModuleName];
                }
            }
        }
        $nodes = array_unique(
            array_merge($nodes, $this->packageInfo->getNonExistingDependencies())
        );

        return new Graph($nodes, $dependencies);
    }
}
