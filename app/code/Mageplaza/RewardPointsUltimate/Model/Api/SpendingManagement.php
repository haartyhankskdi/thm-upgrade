<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
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

namespace Mageplaza\RewardPointsUltimate\Model\Api;

use Magento\Checkout\Api\Data\TotalsInformationInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Quote\Model\Quote;
use Mageplaza\RewardPoints\Model\Api\SpendingManagement as RewardSpendingManagement;
use Mageplaza\RewardPointsUltimate\Helper\SellPoint;

/**
 * Class SpendingManagement
 * @package Mageplaza\RewardPointsProUltimate\Model\Api
 */
class SpendingManagement
{
    /**
     * @var SellPoint
     */
    protected $sellPoint;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var State
     */
    protected $state;

    /**
     * SpendingManagement constructor.
     *
     * @param SellPoint $sellPoint
     * @param Session $customerSession
     * @param ManagerInterface $messageManager
     * @param State $state
     */
    public function __construct(
        SellPoint $sellPoint,
        Session $customerSession,
        ManagerInterface $messageManager,
        State $state
    ) {
        $this->sellPoint = $sellPoint;
        $this->customerSession = $customerSession;
        $this->messageManager = $messageManager;
        $this->state = $state;
    }

    /**
     * @param RewardSpendingManagement $subject
     * @param callable $proceed
     * @param int $cartId
     * @param TotalsInformationInterface $addressInformation
     * @param int $points
     * @param string $ruleId
     *
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function aroundCalculate(
        RewardSpendingManagement $subject,
        callable $proceed,
        $cartId,
        TotalsInformationInterface $addressInformation,
        $points,
        $ruleId
    ) {
        /** @var Quote $quote */
        $quote = $subject->getQuote($cartId);

        if ($this->sellPoint->isValid($points, $quote)) {
            return $proceed($cartId, $addressInformation, $points, $ruleId);
        }

        $message = __('You don\'t have enough points to spend!');
        if ($this->state->getAreaCode() === Area::AREA_WEBAPI_REST) {
            throw new LocalizedException($message);
        }

        $this->messageManager->addNoticeMessage($message);

        return false;
    }
}
