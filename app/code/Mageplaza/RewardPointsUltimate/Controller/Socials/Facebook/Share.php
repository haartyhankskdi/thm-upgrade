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

namespace Mageplaza\RewardPointsUltimate\Controller\Socials\Facebook;

use Magento\Framework\Phrase;
use Mageplaza\RewardPointsUltimate\Controller\Socials;
use Mageplaza\RewardPointsUltimate\Helper\Data;
use Mageplaza\RewardPointsUltimate\Model\Source\CustomerEvents;

/**
 * Class Share
 * @package Mageplaza\RewardPointsUltimate\Controller\Socials\Facebook
 */
class Share extends Socials
{
    /**
     * @return int
     */
    public function getBehaviorAction()
    {
        return CustomerEvents::SHARE_PURCHASE_FACEBOOK;
    }

    /**
     * @return string
     */
    public function getTransactionAction()
    {
        return Data::ACTION_SHARE_FACEBOOK;
    }

    /**
     * @return Phrase
     */
    public function getHasUrlMessage()
    {
        return __("You've already shared this product.");
    }

    /**
     * @param $pointFormat
     *
     * @return Phrase
     */
    public function getCompleteMessageByAction($pointFormat)
    {
        return __("You've earned %1 for sharing the product", $pointFormat);
    }

    /**
     * @return mixed
     */
    public function setLastTime()
    {
        return $this->customerSession->setFbShareLastTime(time());
    }

    /**
     * @return mixed
     */
    public function getLastTime()
    {
        return $this->customerSession->getFbShareLastTime();
    }
}
