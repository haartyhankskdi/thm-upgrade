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
 * @package     Mageplaza_RewardPointsPro
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsPro\Model\Action\Earning;

use Mageplaza\RewardPointsPro\Helper\Data;
use Mageplaza\RewardPoints\Model\Action;
use Mageplaza\RewardPoints\Model\Source\Status;

/**
 * Class Hold
 * @package Mageplaza\RewardPoints\Model\Action\Earning
 */
class Hold extends Action
{
    const CODE = 'holding_points';

    /**
     * @inheritdoc
     */
    public function getActionLabel()
    {
        return __('Holding Points');
    }

    /**
     * @inheritdoc
     */
    public function getTitle($transaction)
    {
        return $this->getComment($transaction, 'Earn points for purchasing order #%1');
    }

    /**
     * @return int
     */
    protected function getStatus()
    {
        return Status::HOLDING;
    }

    /**
     * @return int|mixed
     */
    public function getActionType()
    {
        return Data::ACTION_TYPE_HOLDING;
    }
}
