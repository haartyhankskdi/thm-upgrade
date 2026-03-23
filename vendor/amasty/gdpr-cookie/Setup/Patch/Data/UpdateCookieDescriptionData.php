<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Setup\Patch\Data;

use Amasty\GdprCookie\Model\ResourceModel\Cookie;
use Magento\Framework\Module\ResourceInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class UpdateCookieDescriptionData implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var ResourceInterface
     */
    private $moduleResource;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        ResourceInterface $moduleResource
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->moduleResource = $moduleResource;
    }

    public function apply(): UpdateCookieDescriptionData
    {
        $setupDataVersion = (string)$this->moduleResource->getDataVersion('Amasty_GdprCookie');
        if ($setupDataVersion && version_compare($setupDataVersion, '2.2.0', '<')) {
             $cookieStoreTable = $this->moduleDataSetup->getTable(Cookie::STORE_DATA_TABLE_NAME);
             $this->moduleDataSetup->getConnection()->update(
                 $cookieStoreTable,
                 ['description' => null],
                 ['description = ?' => '']
             );
        }

        return $this;
    }

    public static function getDependencies()
    {
        return [
            MoveCookieData::class
        ];
    }

    public function getAliases()
    {
        return [];
    }
}
