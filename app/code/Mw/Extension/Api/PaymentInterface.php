<?php

namespace Mw\Extension\Api;

interface PaymentInterface
{
    /**
     * @api
     * @return array
     */
    public function getMerchantKey();
}
