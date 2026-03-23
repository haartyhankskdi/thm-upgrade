<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Google Consent Mode
 */

namespace Amasty\GoogleConsentMode\Setup\Patch\Data;

use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class MoveConsents implements DataPatchInterface
{
    /**
     * @var Config
     */
    private $configResource;

    public function __construct(
        Config $configResource
    ) {
        $this->configResource = $configResource;
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }

    public function apply(): self
    {
        $data = $this->getConfigValues();

        foreach ($data as &$record) {
            $record['path'] = str_replace('am_ga4', 'amasty_gdprcookie', $record['path']);
        }

        if ($data) {
            $this->configResource->getConnection()->insertOnDuplicate(
                $this->configResource->getMainTable(),
                $data
            );
        }

        return $this;
    }

    private function getConfigValues(): array
    {
        $connection = $this->configResource->getConnection();
        $select = $connection->select()->from(
            $this->configResource->getMainTable()
        )->where(
            'path like ?',
            'am_ga4/consent_mode%'
        );

        return $connection->fetchAll($select);
    }
}
