<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Plugin\Elasticsearch\Model\Source\FulltextAttributes;

use Amasty\ElasticSearch\Model\Source\FulltextAttributes;
use Amasty\Xsearch\Model\CustomSearchAttributes;

class AddCustomSearchAttributes
{
    /**
     * @var CustomSearchAttributes
     */
    private CustomSearchAttributes $customSearchAttributes;

    public function __construct(CustomSearchAttributes $customSearchAttributes)
    {
        $this->customSearchAttributes = $customSearchAttributes;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetCustomSearchAttributes(FulltextAttributes $subject, array $result): array
    {
        return $result + array_map(function ($attributeConfig) {
            return $attributeConfig['label'];
        }, $this->customSearchAttributes->getAttributes(true));
    }
}
