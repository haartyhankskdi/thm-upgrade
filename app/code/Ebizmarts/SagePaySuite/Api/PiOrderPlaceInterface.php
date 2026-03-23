<?php

namespace Ebizmarts\SagePaySuite\Api;

interface PiOrderPlaceInterface
{
    public function pay($isMoto = false);

    public function processPayment($isMoto = false);

    public function getRequest($isMoto = false);

    /**
     * @return \Ebizmarts\SagePaySuite\Api\Data\PiResultInterface
     */
    public function placeOrder();
}
