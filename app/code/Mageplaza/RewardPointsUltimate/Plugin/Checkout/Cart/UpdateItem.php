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

namespace Mageplaza\RewardPointsUltimate\Plugin\Checkout\Cart;

use Magento\Checkout\Model\Cart;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\RewardPointsUltimate\Helper\Data as HelperData;
use Mageplaza\RewardPointsUltimate\Helper\SellPoint;
use Psr\Log\LoggerInterface;

/**
 * Class UpdateItem
 * @package Mageplaza\RewardPointsUltimate\Plugin\Checkout\Cart
 */
class UpdateItem
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var SellPoint
     */
    protected $sellPoint;

    /**
     * UpdateItem constructor.
     *
     * @param HelperData $helperData
     * @param LoggerInterface $logger
     * @param SellPoint $sellPoint
     */
    public function __construct(HelperData $helperData, LoggerInterface $logger, SellPoint $sellPoint)
    {
        $this->helperData = $helperData;
        $this->logger = $logger;
        $this->sellPoint = $sellPoint;
    }

    /**
     * @param Cart $subject
     * @param $data
     *
     * @return array
     * @throws LocalizedException
     */
    public function beforeUpdateItems(Cart $subject, $data)
    {
        if ($this->helperData->isEnabled() && $this->helperData->getAccountHelper()->isCustomerLoggedIn()) {
            $rewardCustomer = $this->helperData->getAccountHelper()->get();
            $quote = $subject->getQuote();
            $pointSpent = $quote->getMpSpent();
            if ($rewardCustomer->getPointBalance() > 0) {
                foreach ($data as $itemId => $itemInfo) {
                    $item = $quote->getItemById($itemId);
                    if (!$item || $item->getMpRewardSellPoints() < 0.001) {
                        continue;
                    }
                    $pointSpent += ($item->getMpRewardSellPoints() * $itemInfo['qty']);
                }
                if ($rewardCustomer->getPointBalance() < $pointSpent) {
                    throw new LocalizedException(__('You haven\'t enough point update items'));
                }
            }
        }

        return [$data];
    }
}
