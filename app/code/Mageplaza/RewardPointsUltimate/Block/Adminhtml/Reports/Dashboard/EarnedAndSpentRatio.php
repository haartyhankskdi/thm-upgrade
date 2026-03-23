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

namespace Mageplaza\RewardPointsUltimate\Block\Adminhtml\Reports\Dashboard;

use Magento\Framework\Phrase;
use Mageplaza\RewardPointsUltimate\Helper\Data as RewardHelper;
use Zend_Db_Expr;

/**
 * Class Spent
 * @package Mageplaza\RewardPointsUltimate\Block\Adminhtml\Reports\Dashboard
 */
class EarnedAndSpentRatio extends Reports
{
    const MAGE_REPORT_CLASS = EarnedAndSpentRatio::class;
    const COMPONENT_NAME = 'earned-and-spent-chart';

    /**
     * @return Phrase|string
     */
    public function getTitle()
    {
        return __('Spending/Earning Ratio');
    }

    /**
     * @return string
     */
    public function getRate()
    {
        return '';
    }

    /**
     * @return array
     */
    public function getLabelColor()
    {
        return $this->reportsHelper->getLabelColorChart(false);
    }

    /**
     * @param $from
     * @param $to
     *
     * @return array|bool
     */
    public function getRewardData($from, $to)
    {
        $rewardData = [];
        $transactionCollection = $this->addRewardToFilter($from, $to);
        $transactionCollection->getSelect()->columns(
            [
                'earned' => new Zend_Db_Expr(
                    'SUM(CASE WHEN
								  ' . $this->getListStrEarningAction() . '
								THEN main_table.point_amount ELSE 0 END)'
                ),
                'spent' => new Zend_Db_Expr(
                    sprintf(
                        'SUM(CASE WHEN main_table.action_code = \'%s\' THEN ABS(main_table.point_amount) ELSE 0 END )',
                        RewardHelper::ACTION_SPENDING_ORDER
                    )
                )
            ]
        );

        if ($transactionCollection->getSize() === 0) {
            return false;
        }

        foreach ($transactionCollection as $collection) {
            if ($collection->getData('transaction_id')) {
                $rewardData[0] = $collection->getData('earned');
                $rewardData[1] = $collection->getData('spent');
                break;
            }
        }

        return $rewardData;
    }

    /**
     * @return string
     */
    public function getListStrEarningAction()
    {
        $strAction = '';
        $or = ' OR';
        foreach ($this->getMpEarnedFields() as $key => $action) {
            $strAction .= "$or main_table.action_code = '$action' ";
        }

        return substr($strAction, 3);
    }
}
