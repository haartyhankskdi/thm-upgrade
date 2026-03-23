<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Cron;

use Amasty\Base\Model\Feed\FeedTypes\Extensions;

class RefreshFeedData
{
    /**
     * @var Extensions
     */
    private $extensionsFeed;

    public function __construct(
        Extensions $extensionsFeed
    ) {
        $this->extensionsFeed = $extensionsFeed;
    }

    /**
     * Force reload feeds data
     */
    public function execute()
    {
        $this->extensionsFeed->getFeed();
    }
}
