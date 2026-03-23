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
 * @package     Mageplaza_RewardPointsPro
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsPro\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface as CoreCartRepository;
use Magento\Quote\Model\Quote;
use Mageplaza\RewardPoints\Helper\Calculation as HelperCalculation;
use Mageplaza\RewardPointsPro\Api\CartRepositoryInterface;

/**
 * Class CartRepository
 * @package Mageplaza\RewardPointsPro\Model
 */
class CartRepository implements CartRepositoryInterface
{
    /**
     * @var HelperCalculation
     */
    protected $helperCalculation;

    /**
     * @var $cartRepository
     */
    protected $cartRepository;

    /**
     * CartRepository constructor.
     *
     * @param HelperCalculation $helperCalculation
     * @param CoreCartRepository $cartRepository
     */
    public function __construct(
        HelperCalculation $helperCalculation,
        CoreCartRepository $cartRepository
    ) {
        $this->helperCalculation = $helperCalculation;
        $this->cartRepository = $cartRepository;
    }

    /**
     * {@inheritDoc}
     * @throws NoSuchEntityException
     */
    public function getSpendingRuleConfiguration(
        $cartId
    ) {
        if (!$this->helperCalculation->isEnabled()) {
            return [];
        }

        /** @var Quote $quote */
        $quote = $this->cartRepository->get($cartId);

        return [$this->getSpendingRules($quote)];
    }

    /**
     * @param Quote $quote
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getSpendingRules($quote)
    {
        $this->helperCalculation->setQuote($quote);

        return $this->helperCalculation->getSpendingConfiguration($quote);
    }
}
