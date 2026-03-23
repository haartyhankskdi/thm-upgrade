<?php

namespace Mw\Extension\Api;
interface GetProductImageInterface {
    /**
     * @api
     * @param string $sku
     * @return array
     */
    public function getProductImageUrl($sku);
}