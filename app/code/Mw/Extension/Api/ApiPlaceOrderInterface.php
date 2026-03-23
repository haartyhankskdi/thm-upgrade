<?php

namespace Mw\Extension\Api;

interface ApiPlaceOrderInterface
{
    /**
     * @api
     * @return array
     */
    public function placeOrderForCustomer();

    /**
     * @api
     * @return array
     */
    public function orderMail();

    /**
     * @api
     * @return array
     */
    public function getOrderId();
}
