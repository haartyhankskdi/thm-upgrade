<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Setup;

use Amasty\GdprCookie\Setup\Patch\Data\MoveCookieData;
use Amasty\GdprCookie\Setup\Patch\Data\MoveCookieGroupData;
use Magento\Framework\FlagManager;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\PatchHistory;

class RecurringData implements InstallDataInterface
{
    /**
     * @var PatchHistory
     */
    private $patchHistory;

    /**
     * @var FlagManager
     */
    private $flagManager;

    public function __construct(
        PatchHistory $patchHistory,
        FlagManager $flagManager
    ) {
        $this->patchHistory = $patchHistory;
        $this->flagManager = $flagManager;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (!$this->flagManager->getFlagData('am_gdpr_cookie_upg_to_290')) {
            if ($this->removeTables($setup)) {
                $this->flagManager->saveFlag('am_gdpr_cookie_upg_to_290', true);
            }
        }
        $setup->endSetup();
    }

    private function removeTables(ModuleDataSetupInterface $setup): bool
    {
        $tablesToDrop = [
            'amasty_gdprcookie_cookie_description' => MoveCookieData::class,
            'amasty_gdprcookie_cookie_store' => MoveCookieData::class,
            'amasty_gdprcookie_cookie_group_description' => MoveCookieGroupData::class,
            'amasty_gdprcookie_cookie_group_store' => MoveCookieGroupData::class,
            'amasty_gdprcookie_cookie_group_link' => MoveCookieData::class,
            'amasty_gdprcookie_cookie_group_link_store' => MoveCookieData::class,
            'amasty_gdprcookie_group_cookie' => MoveCookieGroupData::class
        ];
        $connection = $setup->getConnection();
        foreach ($tablesToDrop as $table => &$patchName) {
            if ($this->patchHistory->isApplied($patchName)) {
                $tableName = $setup->getTable($table);
                if ($setup->tableExists($tableName)) {
                    $connection->dropTable($tableName);
                }

                unset($tablesToDrop[$table]);
            }
        }

        return empty($tablesToDrop);
    }
}
