<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Plugin\Elasticsearch\SearchAdapter\Query\Builder\Aggregation;

use Amasty\Shopby\Model\ConfigProvider;
use Magento\Elasticsearch\SearchAdapter\Query\Builder\Aggregation;

class SetCategoryBucketSize
{
    public const CATEGORY_BUCKET_SIZE = 'category_bucket';

    /**
     * @var ConfigProvider
     */
    private ConfigProvider $configProvider;

    public function __construct(ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    /**
     * @param Aggregation $subject
     * @param array $result
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterBuild(Aggregation $subject, $result)
    {
        if (isset($result['body']['aggregations'][static::CATEGORY_BUCKET_SIZE]['terms'])) {
            $result['body']['aggregations'][static::CATEGORY_BUCKET_SIZE]['terms']['size']
                = $this->configProvider->getCategoryBucketSize();
        }

        return $result;
    }
}
