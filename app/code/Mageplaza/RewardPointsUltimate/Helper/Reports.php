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

namespace Mageplaza\RewardPointsUltimate\Helper;

/**
 * Class Reports
 * @package Mageplaza\RewardPointsUltimate\Helper
 */
class Reports extends Data
{
    /**
     * @param bool $isEarned
     *
     * @return array
     */
    public function getLabelColorChart($isEarned = true)
    {
        if ($isEarned) {
            $label = $this->getLabelsReportEarned();
            $color = $this->getColorsReportsEarned();
        } else {
            $label = $this->getLabelsReportSpent();
            $color = $this->getColorsReportsSpent();
        }

        return ['labels' => $label, 'colors' => $color];
    }

    /**
     * @return array
     */
    public function getLabelsReportEarned()
    {
        return [
            __('Admin'),
            __('Purchase Order'),
            __('Sign Up'),
            __('Newsletter'),
            __('Review Product'),
            __('Birthday'),
            __('Like Facebook'),
            __('Share Facebook'),
            __('Twitter'),
            __('Referral Earn')
        ];
    }

    /**
     * @return array
     */
    public function getColorsReportsEarned()
    {
        return [
            'rgba(255,0,0,0.5)',
            '#33CB80',
            '#F1B55F',
            '#2D7B78',
            '#538622',
            '#5D2286',
            '#227186',
            '#FB5B04',
            '#DC235C',
            '#F7F5AF',
            '#51A9FF'
        ];
    }

    /**
     * @return array
     */
    public function getLabelsReportSpent()
    {
        return [__('Earned'), __('Spent')];
    }

    /**
     * @return array
     */
    public function getColorsReportsSpent()
    {
        return ['rgba(255,0,0,0.5)', '#33CB80'];
    }
}
