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

namespace Mageplaza\RewardPointsUltimate\Model\Source;

/**
 * Class PointSource
 * @package Mageplaza\RewardPointsUltimate\Model\Source
 */
class PointSource extends AbstractSource
{
    const ADMIN = 0;
    const BIRTHDAY = 1;
    const PURCHASE = 2;
    const REVIEW = 3;
    const SIGN_UP = 4;
    const NEWSLETTER = 5;
    const LIKE_FACEBOOK = 6;
    const SHARE_FACEBOOK = 7;
    const TWITTER = 8;
    const REFERRAL = 9;
    const COMEBACK = 10;
    const SEND_EMAIL = 11;
    const EARN_REFUND = 12;
    const LIFETIME_AMOUNT = 13;

    /**
     * Retrieve option array
     *
     * @return string[]
     */
    public static function getOptionArray()
    {
        return [
            self::ADMIN => __('Admin added'),
            self::BIRTHDAY => __('Birthday'),
            self::PURCHASE => __('Purchase'),
            self::REVIEW => __('Review'),
            self::SIGN_UP => __('Sign up'),
            self::NEWSLETTER => __('Newsletter subscriber'),
            self::LIKE_FACEBOOK => __('Facebook like'),
            self::SHARE_FACEBOOK => __('Facebook share'),
            self::TWITTER => __('Twitter tweet'),
            self::REFERRAL => __('Referral'),
            self::COMEBACK => __('Customer comeback'),
            self::SEND_EMAIL => __('Send email to friend'),
            self::EARN_REFUND => __('Earn Refund'),
            self::LIFETIME_AMOUNT => __('Lifetime Amount'),
        ];
    }

    /**
     * Retrieve option array with empty value
     *
     * @return string[]
     */
    public function toOptionArray()
    {
        $result = [];

        foreach (self::getOptionArray() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }

        return $result;
    }
}
