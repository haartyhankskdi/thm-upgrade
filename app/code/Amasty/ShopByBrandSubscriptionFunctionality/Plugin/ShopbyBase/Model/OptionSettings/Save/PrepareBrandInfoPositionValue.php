<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Shop by Brand Subscription Functionality
 */

namespace Amasty\ShopByBrandSubscriptionFunctionality\Plugin\ShopbyBase\Model\OptionSettings\Save;

use Amasty\ShopbyBase\Api\Data\OptionSettingInterface;
use Amasty\ShopbyBase\Model\OptionSettings\Save as OptionSettingsSave;

class PrepareBrandInfoPositionValue
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSaveData(
        OptionSettingsSave $subject,
        string $attributeCode,
        int $optionId,
        int $storeId,
        array $data
    ): array {
        if (isset($data[OptionSettingInterface::BRAND_INFO_BLOCK_POSITION])) {
            $data[OptionSettingInterface::BRAND_INFO_BLOCK_POSITION] = implode(
                ',',
                $data[OptionSettingInterface::BRAND_INFO_BLOCK_POSITION]
            );
        }

        return [$attributeCode, $optionId, $storeId, $data];
    }
}
