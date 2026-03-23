<?php

namespace Ebizmarts\SagePaySuite\Setup\Patch\Schema;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;

class UpdateColumnSagepayProtocol implements SchemaPatchInterface
{
    /** @var ModuleDataSetupInterface $moduleDataSetup */
    private $moduleDataSetup;

    /**
     * UpdateColumnSagepayProtocol constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }

    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        $select = $this->moduleDataSetup->getConnection()->select()->from(
            $this->moduleDataSetup->getTable('core_config_data'),
            ['config_id', 'value']
        )->where(
            'path = ?',
            'sagepaysuite/global/protocol'
        );

        foreach ($this->moduleDataSetup->getConnection()->fetchAll($select) as $configRow) {
            if ($configRow['value'] === '3.00') {
                $row = [
                    'value' => '4.00'
                ];
                $this->moduleDataSetup->getConnection()->update(
                    $this->moduleDataSetup->getTable('core_config_data'),
                    $row,
                    ['config_id = ?' => $configRow['config_id']]
                );
            }
        }

        $this->moduleDataSetup->endSetup();
    }
}
