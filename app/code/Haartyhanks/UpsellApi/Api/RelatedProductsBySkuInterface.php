<?php
namespace Haartyhanks\UpsellApi\Api;

interface RelatedProductsBySkuInterface
{
    /**
     * @param string $sku
     * @return mixed
     */
    public function getRelatedProducts($sku);
}
