<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search and Elastic Search GraphQL for Magento 2 (System)
 */

use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Customer/_files/new_customer_rollback.php');
Resolver::getInstance()->requireDataFixture(
    'Amasty_ElasticSearchGraphQl::Test/GraphQl/_files/popular_queries_rollback.php'
);
