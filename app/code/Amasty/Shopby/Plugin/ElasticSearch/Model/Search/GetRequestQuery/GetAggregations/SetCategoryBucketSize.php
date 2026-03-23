<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Improved Layered Navigation Base for Magento 2
 */

namespace Amasty\Shopby\Plugin\ElasticSearch\Model\Search\GetRequestQuery\GetAggregations;

use Amasty\ElasticSearch\Model\Search\GetRequestQuery\GetAggregations;
use Amasty\Shopby\Model\ConfigProvider;

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
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(GetAggregations $subject, array $result): array
    {
        if (isset($result[static::CATEGORY_BUCKET_SIZE]['terms'])) {
            $result[static::CATEGORY_BUCKET_SIZE]['terms']['size'] = $this->configProvider->getCategoryBucketSize();
        }

        return $result;
    }
}
