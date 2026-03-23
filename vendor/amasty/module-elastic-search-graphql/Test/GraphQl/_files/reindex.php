<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search and Elastic Search GraphQL for Magento 2 (System)
 */

use Magento\Framework\Shell;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var Shell $shell*/
$shell = $objectManager->get(Shell::class);

$indexes = [
    'amasty_xsearch_category_fulltext',
    'catalogrule_product',
    'catalogrule_rule',
    'catalogsearch_fulltext',
    'catalog_category_product',
    'catalog_product_attribute',
    'inventory',
    'catalog_product_price',
    'cataloginventory_stock'
];
$parameters = implode(' ', $indexes);

$appDir = dirname(Bootstrap::getInstance()->getAppTempDir());
$shell->execute("php -f {$appDir}/bin/magento indexer:reindex {$parameters}");
