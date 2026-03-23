<?php

namespace Ebizmarts\SagePaySuite\Api;

use Ebizmarts\SagePaySuite\Api\Data\PayPalRequest;

/**
 * @api
 */
interface PayPalManagementInterface
{

    /**
     * @param string $cartId
     * @param PayPalRequest $requestData
     * @return \Ebizmarts\SagePaySuite\Api\Data\ResultInterface
     */
    public function savePaymentInformationAndPlaceOrder($cartId, $requestData);

    /**
     * @param string $cartId
     * @return \Magento\Quote\Api\Data\CartInterface
     */
    public function getQuoteById($cartId);
}
