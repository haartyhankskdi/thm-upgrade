<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_RewardPointsPro
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsPro\Helper;

use DateTime;
use Exception;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\Data\StoreInterface;
use Mageplaza\RewardPoints\Helper\Validate as RewardValidate;

/**
 * Class Validate
 * @package Mageplaza\RewardPointsPro\Helper
 */
class Validate extends RewardValidate
{
    /**
     * @param array $data
     * @param String $field
     *
     * @throws InputException
     */
    public function validateZeroOrGreater($data, $field)
    {
        if (isset($data[$field]) && $data[$field] < 0) {
            throw new InputException(__('%1 must be greater or equal zero.', $field));
        }
    }

    /**
     * @param array $data
     *
     * @throws InputException
     * @throws LocalizedException
     */
    public function validateFromAndToDate($data)
    {
        try {
            $fromDate = new DateTime($data['from_date']);
            $toDate = new DateTime($data['to_date']);
        } catch (Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }

        if ($fromDate > $toDate) {
            throw new InputException(__(__('End Date must follow Start Date.')));
        }
    }

    /**
     * @param string $date
     *
     * @return DateTime|false
     * @throws LocalizedException
     */
    public function isValidDate($date)
    {
        try {
            $isValid = date_create($date);
        } catch (Exception $e) {
            $isValid = false;
        }

        if (!$isValid) {
            throw  new LocalizedException(__('Invalid date'));
        }

        return true;
    }

    /**
     * @param int $storeId
     *
     * @return StoreInterface
     * @throws NoSuchEntityException
     */
    public function getStoreById($storeId)
    {
        return $this->storeManager->getStore($storeId);
    }

    /**
     * @param array $data
     *
     * @throws InputException
     * @throws LocalizedException
     */
    public function validateGeneral($data)
    {
        if (isset($data['sort_order'])) {
            $this->validateZeroOrGreater($data, 'sort_order');
        }

        if (isset($data['from_date'])) {
            $this->isValidDate($data['from_date']);
        }

        if (isset($data['to_date'])) {
            $this->isValidDate($data['to_date']);
        }

        if (isset($data['from_date'], $data['to_date'])) {
            $this->validateFromAndToDate($data);
        }

        $this->validateWebsiteIds($data);
        $this->validateCustomerGroupIds($data);
        $this->validateGreaterThanZero($data, 'point_amount');
    }
}
