<?php

namespace Mw\Extension\Api;

interface GetConfigInterface
{
    /**
     * @api
     * @return array
     */
    public function getFreeShippingRate();

     /**
     * @api
     * @return array
     */
    public function getBraintreeNounce();
}
