<?php

namespace Ebizmarts\Notification\Model\Module;

use Magento\Framework\Module\ModuleList;

class Management
{
    /** @var ModuleList $moduleList */
    private $moduleList;

    /** @var string[] $availableModules */
    private $availableModules;

    /**
     * @param ModuleList $moduleList
     * @param $availableModules
     */
    public function __construct(
        ModuleList $moduleList,
        $availableModules = []
    ) {
        $this->moduleList = $moduleList;
        $this->availableModules = $availableModules;
    }

    /**
     * @return string[]
     */
    public function getEbizmartsModules()
    {
        $modules = $this->moduleList->getAll();
        $ebizmartsModules = [];
        foreach ($modules as $moduleName => $module) {
            if (in_array($moduleName, $this->availableModules)) {
                $ebizmartsModules []= array_search($moduleName, $this->availableModules) . '.xml';
            }
        }

        return $ebizmartsModules;
    }

    /**
     * @param string $file
     * @return bool
     */
    public function fileContainsModuleName($file)
    {
        $contains = false;
        foreach ($this->availableModules as $key => $value) {
            if (strpos($file, $key) !== false) {
                $contains = true;
                break;
            }
        }

        return $contains;
    }
}
