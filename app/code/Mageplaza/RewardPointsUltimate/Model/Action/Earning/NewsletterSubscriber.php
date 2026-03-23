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

namespace Mageplaza\RewardPointsUltimate\Model\Action\Earning;

use Mageplaza\RewardPoints\Model\Action\Earning;

/**
 * Class NewsletterSubscriber
 * @package Mageplaza\RewardPointsUltimate\Model\Action\Earning
 */
class NewsletterSubscriber extends Earning
{
    const CODE = 'earning_newsletter_subscriber';

    /**
     * @inheritdoc
     */
    public function getActionLabel()
    {
        return __('Newsletter Subscriber');
    }

    /**
     * @inheritdoc
     */
    public function getTitle($transaction)
    {
        return __('Earn points for newsletter subscriber');
    }

    /**
     * @return null
     */
    protected function getExpirationDate()
    {
        $expireDate  = null;
        $expireAfter = $this->getActionObject()->getData('expireAfter');
        if ($expireAfter && $expireAfter !== 0) {
            $expireDate = date('Y-m-d H:i:s', strtotime("+{$expireAfter}days"));
        }

        return $expireDate;
    }
}
