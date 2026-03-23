<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Model\ResourceModel;

use Ebizmarts\SagePaySuite\Model\Config;

/**
 * Token resource model
 */
class Token extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    // @codingStandardsIgnoreStart
    protected function _construct()
    {
        $this->_init('sagepaysuite_token', 'id');
    }
    // @codingStandardsIgnoreEnd

    /**
     * Get tokens by customer id and vendorname
     * @param \Ebizmarts\SagePaySuite\Model\Token $object
     * @param $customerId
     * @param $vendorname
     * @return array
     */
    public function getCustomerTokens(\Ebizmarts\SagePaySuite\Model\Token $object, $customerId, $vendorname)
    {
        $connection = $this->getConnection();
        $select     = $connection->select()
            ->from($this->getMainTable())
            ->where('customer_id=?', $customerId)
            ->where('vendorname=?', $vendorname)
            ->where('payment_method=?', Config::METHOD_SERVER);

        $data = [];

        $query = $connection->query($select);
        while ($row = $query->fetch()) {
            array_push($data, $row);
        }

        if (count($data)) {
            $object->setData($data);
        }

        $this->_afterLoad($object);

        return $data;
    }

    /**
     * Get tokens by customer id and vendorname
     * @param $tokenId
     * @return array
     */
    public function getTokenById($tokenId)
    {
        $connection = $this->getConnection();
        $select     = $connection->select()->from($this->getMainTable())->where('id=?', $tokenId);

        $data = $connection->fetchRow($select);

        return $data;
    }

    /**
     * Checks if token is owned by customer
     * @param $customerId
     * @param $tokenId
     * @return bool
     */
    public function isTokenOwnedByCustomer($customerId, $tokenId)
    {
        $connection = $this->getConnection();
        $select     = $connection->select()
            ->from($this->getMainTable(), 'id')
            ->where('customer_id=?', $customerId)
            ->where('id=?', $tokenId);

        $data = $connection->fetchOne($select);

        return ($data !== false);
    }

    /**
     * Get token by customer id and token
     * @param $token
     * @return array
     */
    public function getTokenByToken($token)
    {
        $connection = $this->getConnection();
        $select     = $connection->select()
            ->from($this->getMainTable())
            ->where('token=?', '{' . $token . '}');

        $data = $connection->fetchRow($select);

        return $data;
    }

    /**
     * getExpiredTokens function $currentDate param must be on the following format: 'Y-m-01'
     *
     * @param \Ebizmarts\SagePaySuite\Model\Token $object
     * @param $currentDate
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Db_Statement_Exception
     */
    public function getExpiredTokens($object, $currentDate)
    {
        $connection = $this->getConnection();
        $select     = $connection->select()
            ->from($this->getMainTable())
            ->where("CAST(CONCAT_WS('-', cc_exp_year, LPAD(cc_exp_month,2,'0'), '01') as DATE) <?", $currentDate)
            ->limit(50);

        $data = [];

        $query = $connection->query($select);
        while ($row = $query->fetch()) {
            array_push($data, $row);
        }

        if (count($data)) {
            $object->setData($data);
        }

        $this->_afterLoad($object);

        return $data;
    }
}
