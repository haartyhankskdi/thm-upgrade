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
 * @package     Mageplaza_RewardPointsUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsUltimate\Plugin\Controller\Adminhtml\Group;

use Exception;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\Data\Group;
use Mageplaza\RewardPointsUltimate\Helper\Data;

/**
 * Class Save
 * @package Mageplaza\RewardPointsUltimate\Plugin\Controller\Adminhtml\Group
 */
class Save
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * Save constructor.
     *
     * @param Data $helperData
     */
    public function __construct(
        Data $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * @param GroupRepositoryInterface $subject
     * @param Group $result
     *
     * @return mixed
     * @throws Exception
     */
    public function afterSave(GroupRepositoryInterface $subject, $result)
    {
        $this->helperData->updateCustomerGroupAndWebsiteForBaseMileStone();

        return $result;
    }
}
