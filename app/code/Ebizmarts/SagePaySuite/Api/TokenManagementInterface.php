<?php

namespace Ebizmarts\SagePaySuite\Api;

interface TokenManagementInterface
{
    /**
     * @param string $token
     * @return string
     */
    public function deleteFromSagePay($token);

    /**
     * @param string $tokenId
     * @param string $customerId
     * @param string $paymentMethod
     * @return \Ebizmarts\SagePaySuite\Api\Data\ResultInterface
     */
    public function deleteToken($tokenId, $customerId, $paymentMethod);
}
