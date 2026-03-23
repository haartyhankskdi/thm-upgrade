<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Setup\Patch\Data;

use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class RemoveCookieWallConfigData implements DataPatchInterface
{
    private const COOKIE_WALL_CONFIG_PATHS = [
        'amasty_gdprcookie/cookie_policy/website_interaction',
        'amasty_gdprcookie/cookie_policy/allowed_urls'
    ];

    /**
     * @var ConfigInterface
     */
    private $resourceConfig;

    public function __construct(ConfigInterface $resourceConfig)
    {
        $this->resourceConfig = $resourceConfig;
    }

    public function apply(): RemoveCookieWallConfigData
    {
        $connection = $this->resourceConfig->getConnection();

        foreach (self::COOKIE_WALL_CONFIG_PATHS as $path) {
            $connection->delete(
                $this->resourceConfig->getMainTable(),
                'path like \'' . $path . '%\''
            );
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
