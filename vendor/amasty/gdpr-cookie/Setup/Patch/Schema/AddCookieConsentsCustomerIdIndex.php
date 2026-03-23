<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Cookie Consent (GDPR) for Magento 2
 */

namespace Amasty\GdprCookie\Setup\Patch\Schema;

use Amasty\GdprCookie\Api\Data\CookieConsentInterface;
use Amasty\GdprCookie\Model\ResourceModel\CookieConsent as CookieConsentResource;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class AddCookieConsentsCustomerIdIndex implements SchemaPatchInterface
{
    /**
     * @var SchemaSetupInterface
     */
    private $schemaSetup;

    public function __construct(
        SchemaSetupInterface $schemaSetup
    ) {
        $this->schemaSetup = $schemaSetup;
    }

    public function apply(): AddCookieConsentsCustomerIdIndex
    {
        if ($this->schemaSetup->tableExists(CookieConsentResource::TABLE_NAME)) {
            $this->schemaSetup->startSetup();
            // Skip deleting an index if it exists after deleting the foreign key constraint
            if (!$this->isIndexExists()) {
                $this->schemaSetup->getConnection()->addIndex(
                    $this->schemaSetup->getTable(CookieConsentResource::TABLE_NAME),
                    $this->schemaSetup->getIdxName(
                        CookieConsentResource::TABLE_NAME,
                        [CookieConsentInterface::CUSTOMER_ID],
                        AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    [CookieConsentInterface::CUSTOMER_ID],
                    AdapterInterface::INDEX_TYPE_INDEX
                );
            }

            $this->schemaSetup->endSetup();
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

    private function isIndexExists(): bool
    {
        $connection = $this->schemaSetup->getConnection();
        $indexList = $connection->getIndexList($this->schemaSetup->getTable(CookieConsentResource::TABLE_NAME));
        foreach ($indexList as $indexData) {
            if ($indexData['INDEX_TYPE'] === AdapterInterface::INDEX_TYPE_INDEX
                && $indexData['COLUMNS_LIST'] === [CookieConsentInterface::CUSTOMER_ID]
            ) {
                return true;
            }
        }

        return false;
    }
}
