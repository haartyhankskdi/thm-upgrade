<?php
namespace Kdi\Category\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    /**
     * Get config value by path
     *
     * @param string $configPath
     * @param int|null $storeId
     * @return mixed
     */
    public function getConfigValue($configPath, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $configPath,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if config is set to 1 (Enabled)
     *
     * @param string $configPath
     * @param int|null $storeId
     * @return bool
     */
    public function isConfigEnabled($configPath, $storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            $configPath,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
