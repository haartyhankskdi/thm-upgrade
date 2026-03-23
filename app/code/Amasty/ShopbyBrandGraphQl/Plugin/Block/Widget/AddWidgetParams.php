<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Amasty Shop By Brand GraphQl for Magento 2 (System)
 */

namespace Amasty\ShopbyBrandGraphQl\Plugin\Block\Widget;

use Amasty\ShopbyBrand\Block\Widget\BrandListAbstract;
use Magento\Framework\Serialize\Serializer\Json;

class AddWidgetParams
{
    /**
     * @var Json
     */
    private $json;

    public function __construct(Json $json)
    {
        $this->json = $json;
    }

    public function afterToHtml(BrandListAbstract $subject, string $html): string
    {
        $html = sprintf(
            '<div class="ambrand-widget-wrapper"><script>var amBrandConfig%s = "%s"</script>%s</div>',
            uniqid(),
            $this->json->serialize($subject->getData()),
            $html
        );

        return $html;
    }
}
