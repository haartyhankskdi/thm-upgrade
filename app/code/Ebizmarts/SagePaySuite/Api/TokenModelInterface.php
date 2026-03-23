<?php

namespace Ebizmarts\SagePaySuite\Api;

use Ebizmarts\SagePaySuite\Api\Data\TokenInterface;
use Magento\Framework\Exception\NoSuchEntityException;

interface TokenModelInterface
{
    /**
     * Saves a token to the db
     *
     * @param TokenInterface $tokenInterface
     */
    public function saveToken($tokenInterface);

    /**
     * Gets an array of the tokens owned by a customer and for a certain vendorname
     *
     * @param int $customerId
     * @param string $vendorname
     * @return array
     */
    public function getCustomerTokens($customerId, $vendorname);

    /**
     * @param int $customerId
     * @param string $vendorname
     * @return array
     */
    public function getCustomerTokensToShowOnAccount($customerId, $vendorname);

    /**
     * Delete token from db and Sage Pay
     * @throws NoSuchEntityException
     * @throws \Exception
     */
    public function deleteToken();

    /**
     * load from db
     *
     * @param int $tokenId
     * @return \Ebizmarts\SagePaySuite\Model\Token
     */
    public function loadToken($tokenId);

    /**
     * Checks whether the token is owned by the customer
     *
     * @param int $customerId
     * @return bool
     */
    public function isOwnedByCustomer($customerId);

    /**
     * Checks whether the customer is using all the available token slots.
     * @param int $customerId
     * @param string $vendorname
     * @return bool
     */
    public function isCustomerUsingMaxTokenSlots($customerId, $vendorname);

    /**
     * @param string $token
     */
    public function loadTokenByToken($token);

    /**
     * Gets an array of expired tokens $currentDate param must be on the following format: 'Y-m-01'
     *
     * @param $currentDate
     * @return array
     */
    public function getExpiredTokens($currentDate);
}
