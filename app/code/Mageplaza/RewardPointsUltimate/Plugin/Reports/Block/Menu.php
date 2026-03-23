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
 * @category  Mageplaza
 * @package   Mageplaza_RewardPointsUltimate
 * @copyright Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license   https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsUltimate\Plugin\Reports\Block;

use Magento\Framework\App\RequestInterface;
use Mageplaza\RewardPointsUltimate\Helper\Data;

/**
 * Class Menu
 * @package Mageplaza\RewardPointsUltimate\Plugin\Reports\Block
 */
class Menu
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * Menu constructor.
     *
     * @param RequestInterface $request
     * @param Data $helperData
     */
    public function __construct(
        RequestInterface $request,
        Data $helperData
    ) {
        $this->request    = $request;
        $this->helperData = $helperData;
    }

    /**
     * @param \Mageplaza\Reports\Block\Menu $menu
     * @param $result
     *
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetGridName(\Mageplaza\Reports\Block\Menu $menu, $result)
    {
        if (!$this->helperData->isEnabled()) {
            return $result;
        }

        if ($this->request->getFullActionName() === 'mprewardultimate_reports_earned') {
            $result = 'mprewardultimate_reports_earned_grid.mp_reward_reports_earned_listing_data_source';
        }

        if ($this->request->getFullActionName() === 'mprewardultimate_reports_spent') {
            $result = 'mprewardultimate_reports_spent_grid.mp_reward_reports_spent_listing_data_source';
        }

        return $result;
    }
}
