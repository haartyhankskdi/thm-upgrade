<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Model\Schedule;

use Amasty\Storelocator\Model\ConfigProvider;

class FormatTime
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    public function execute(string $time): string
    {
        return $this->configProvider->getConvertTime() ?
            date('g:i a', strtotime($time)) :
            $time;
    }
}
