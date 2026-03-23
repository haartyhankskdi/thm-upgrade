<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Model\Indexer\Data;

use Magento\Elasticsearch\Model\Adapter\BatchDataMapperInterface;

class ElasticsearchMapper extends DefaultMapper implements BatchDataMapperInterface
{
}
