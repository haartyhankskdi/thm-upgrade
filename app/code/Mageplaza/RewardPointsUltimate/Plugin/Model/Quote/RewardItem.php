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

namespace Mageplaza\RewardPointsUltimate\Plugin\Model\Quote;

use Magento\Framework\App\RequestInterface;
use Magento\Quote\Model\Quote\Item;
use Mageplaza\RewardPointsUltimate\Helper\Data;

/**
 * Class RewardItem
 * @package Mageplaza\RewardPointsUltimate\Plugin\Model\Quote
 */
class RewardItem
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * RewardItem constructor.
     *
     * @param Data $helperData
     * @param RequestInterface $request
     */
    public function __construct(
        Data $helperData,
        RequestInterface $request
    ) {
        $this->request    = $request;
        $this->helperData = $helperData;
    }

    /**
     * @param Item $subject
     * @param bool $result
     *
     * @return mixed
     */
    public function afterRepresentProduct(Item $subject, $result)
    {
        if ($this->helperData->isEnabled($subject->getStoreId())) {
            $params = $this->request->getParams();
            if (isset($params['mp_sell_product_by'])) {
                if (((int) $params['mp_sell_product_by'] && !$subject->getMpRewardSellPoints())
                    || (!(int) $params['mp_sell_product_by'] && $subject->getMpRewardSellPoints() > 0)) {
                    return false;
                }
            }
        }

        return $result;
    }
}
