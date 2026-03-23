<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package GDPR Base for Magento 2
 */

namespace Amasty\Gdpr\Setup\Patch\Data;

use Magento\Framework\DB\Select;
use Magento\Framework\Module\ModuleList;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;

class UpdateCoreConfigPath implements DataPatchInterface
{
    /**
     * @var ConfigInterface
     */
    private $resourceConfig;

    public function __construct(ConfigInterface $resourceConfig)
    {
        $this->resourceConfig = $resourceConfig;
    }

    public function apply(): UpdateCoreConfigPath
    {
        //add deny_ to 'sender', 'reply_to', 'template', for version < 1.1.5
        $path = 'amasty_gdpr/deletion_notification/';
        $fields = ['sender', 'reply_to', 'template'];
        $connection = $this->resourceConfig->getConnection();

        $select = $connection->select();
        $select
            ->from($this->resourceConfig->getMainTable())
            ->where("path like '{$path}%'")
            ->reset(Select::COLUMNS)
            ->columns(['path']);
        $configPaths = $connection->fetchAssoc($select);

        foreach ($fields as $fieldId) {
            if (isset($configPaths[$path . 'deny_' . $fieldId])
                || !isset($configPaths[$path . $fieldId])
            ) {
                continue;
            }

            $data = ['path' => $path . 'deny_' . $fieldId];
            $whereCondition = ['path = ?' => $path . $fieldId];
            $connection->update($this->resourceConfig->getMainTable(), $data, $whereCondition);
        }

        return $this;
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}
