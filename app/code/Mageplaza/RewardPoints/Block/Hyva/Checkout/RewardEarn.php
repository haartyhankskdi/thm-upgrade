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
 * @package     Mageplaza_RewardPoints
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPoints\Block\Hyva\Checkout;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Checkout\Model\Session as SessionCheckout;

/**
 * Class RewardEarn
 * @package Mageplaza\RewardPoints\Block\Hyva\Checkout
 */
class RewardEarn extends Template
{
    /**
     * @var CartTotalRepositoryInterface
     */
    protected $cartTotalRepository;

    /**
     * @var SessionCheckout
     */
    protected $sessionCheckout;

    /**
     * @param Context $context
     * @param CartTotalRepositoryInterface $cartTotalRepository
     * @param SessionCheckout $sessionCheckout
     */
    public function __construct(
        Context $context,
        CartTotalRepositoryInterface $cartTotalRepository,
        SessionCheckout $sessionCheckout
    ) {
        $this->cartTotalRepository = $cartTotalRepository;
        $this->sessionCheckout     = $sessionCheckout;
        parent::__construct($context);
    }

    /**
     * @return array|null
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getRewardPointsData()
    {
        try {
            $quote = $this->sessionCheckout->getQuote();
            if (!$quote->getId()) {
                return null;
            }

            $totals       = $this->cartTotalRepository->get($quote->getId());
            $rewardPoints = $totals->getExtensionAttributes()->getRewardPoints();

            if (!$rewardPoints) {
                return null;
            }

            return is_string($rewardPoints) ? json_decode($rewardPoints, true) : $rewardPoints;
        } catch (\Exception $e) {
            return null;
        }
    }
}
