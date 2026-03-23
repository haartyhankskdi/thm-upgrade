<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Model\Uninstall;

use Magento\Framework\Setup\Declaration\Schema\Diff\SchemaDiff;
use Magento\Framework\Setup\Declaration\Schema\OperationsExecutor;
use Magento\Framework\Setup\Declaration\Schema\SchemaConfig;
use Magento\Framework\Setup\Declaration\Schema\SchemaConfigInterfaceFactory;

/**
 * Marking module to ignore its db_schema.xml and run DB schema comparison.
 *
 * @since 1.21.0
 */
class DeclarationDbSchema
{
    public const DEFAULT_REQUEST = [
        'keep-generated' => false,
        'convert-old-scripts' => false,
        'safe-mode' => null,
        'data-restore' => null,
        'dry-run' => false,
        'magento-init-params' => null,
        'help' => false,
        'quiet' => false,
        'verbose' => false,
        'version' => false,
        'ansi' => null,
        'no-interaction' => false
    ];

    /**
     * @var SchemaConfigInterfaceFactory
     */
    private SchemaConfigInterfaceFactory $schemaConfigFactory;

    /**
     * @var SchemaDiff
     */
    private SchemaDiff $schemaDiff;

    /**
     * @var OperationsExecutor
     */
    private OperationsExecutor $operationsExecutor;

    /**
     * @var array
     */
    private array $requestData;

    /**
     * @var Registry
     */
    private Registry $registry;

    public function __construct(
        SchemaConfigInterfaceFactory $schemaConfigFactory,
        SchemaDiff $schemaDiff,
        OperationsExecutor $operationsExecutor,
        Registry $registry,
        array $requestData = []
    ) {
        $this->schemaConfigFactory = $schemaConfigFactory;
        $this->schemaDiff = $schemaDiff;
        $this->operationsExecutor = $operationsExecutor;
        $this->requestData = $requestData;
        $this->registry = $registry;
    }

    public function uninstallModule(string $moduleName): void
    {
        $this->registry->addModule($moduleName);
        /** @var SchemaConfig $schemaConfig */
        $schemaConfig = $this->schemaConfigFactory->create();
        $declarativeSchema = $schemaConfig->getDeclarationConfig();
        $dbSchema = $schemaConfig->getDbConfig();
        $diff = $this->schemaDiff->diff($declarativeSchema, $dbSchema);
        $this->operationsExecutor->execute($diff, $this->getRequest());
        // do not unregister module because unistall can be called in a cycle
    }

    private function getRequest(): array
    {
        return array_merge(self::DEFAULT_REQUEST, $this->requestData);
    }
}
