<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Model\Uninstall;

/**
 * @since 1.21.0
 */
class Registry
{
    /**
     * Module names currently being uninstalled
     * e.g. ['Amasty_Base', 'Amasty_Shopby']
     *
     * @var string[]
     */
    private array $unistallModules = [];

    public function addModule(string $moduleName): void
    {
        $this->unistallModules[$moduleName] = $moduleName;
    }

    public function getModules(): array
    {
        return $this->unistallModules;
    }

    public function clear(): void
    {
        $this->unistallModules = [];
    }

    public function unregister(string $moduleName): void
    {
        $key = array_search($moduleName, $this->unistallModules);
        if ($key !== false) {
            unset($this->unistallModules[$key]);
        }
    }
}
