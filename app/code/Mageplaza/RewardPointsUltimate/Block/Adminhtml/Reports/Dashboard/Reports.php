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

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Phrase;
use Mageplaza\Reports\Helper\Data;
use Mageplaza\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory;
use Mageplaza\RewardPointsUltimate\Helper\Reports as ReportsHelper;
use Zend_Db_Expr;

/**
 * Class Reports
 * @package Mageplaza\RewardPointsUltimate\Block\Adminhtml\Reports\Dashboard
 */
class Reports extends Template
{
    const MAGE_REPORT_CLASS = Earned::class;
    const COMPONENT_NAME = 'earned-chart';

    /**
     * @var string
     */
    protected $_template = 'Mageplaza_RewardPointsUltimate::reports/dashboard.phtml';

    /**
     * @var CollectionFactory
     */
    protected $collection;

    /**
     * @var ReportsHelper
     */
    protected $reportsHelper;

    /**
     * Reports constructor.
     *
     * @param Context $context
     * @param CollectionFactory $collection
     * @param ReportsHelper $reportsHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        CollectionFactory $collection,
        ReportsHelper $reportsHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->collection = $collection;
        $this->reportsHelper = $reportsHelper;
    }

    /**
     * @return string
     */
    public function getComponentName()
    {
        return static::COMPONENT_NAME;
    }

    /**
     * @return mixed
     */
    public function getReportsHelper()
    {
        return ObjectManager::getInstance()->get(Data::class);
    }

    /**
     * @return mixed
     */
    public function isEnabledChart()
    {
        return $this->getReportsHelper()->isEnabledChart();
    }

    /**
     * @return Phrase|string
     */
    public function getTitle()
    {
        return __('Earnings Distribution');
    }

    /**
     * @inheritdoc
     */
    public function getContentHtml()
    {
        if (static::MAGE_REPORT_CLASS) {
            return $this->getLayout()->createBlock(static::MAGE_REPORT_CLASS)->toHtml();
        }

        return $this->toHtml();
    }

    /**
     * @return bool
     */
    public function canShowDetail()
    {
        return false;
    }

    /**
     * @return int
     */
    public function getY()
    {
        return 22;
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return 3;
    }

    /**
     * @return int
     */
    public function getHeight()
    {
        return 13;
    }

    /**
     * @return bool|string
     */
    public function mpRewardChartData()
    {
        $date = $this->getReportsHelper()->getDateRange();
        $data = $this->getRewardData($date[0], $date[1]);
        $compareData = $this->getRewardData($date[2], $date[3]);
        if ($data || $compareData) {
            return ReportsHelper::jsonEncode(
                [
                    'labelColor' => $this->getLabelColor(),
                    'data' => $data,
                    'compareData' => $compareData,
                    'isCompare' => $this->getReportsHelper()->isCompare(),
                    'maintainAspectRatio' => false
                ]
            );
        }

        return false;
    }

    /**
     * @return array
     */
    public function getLabelColor()
    {
        return $this->reportsHelper->getLabelColorChart();
    }

    /**
     * @param $from
     * @param $to
     *
     * @return array
     */
    public function getRewardData($from, $to)
    {
        $transactionCollection = $this->addRewardToFilter($from, $to);
        $rewardData = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        foreach ($this->getMpEarnedFields() as $value) {
            $transactionCollection->getSelect()->columns(
                [
                    $value => new Zend_Db_Expr(
                        sprintf(
                            'SUM(CASE WHEN main_table.action_code = \'%s\' THEN main_table.point_amount ELSE 0 END )',
                            $value
                        )
                    )
                ]
            );
        }
        if ($transactionCollection->getSize() == 0) {
            return false;
        }

        foreach ($transactionCollection as $collection) {
            foreach ($this->getMpEarnedFields() as $key => $value) {
                $rewardData[$key] += intval($collection->getData($value));
            }
        }

        return $rewardData;
    }

    /**
     * @param $from
     * @param $to
     *
     * @return $this
     */
    public function addRewardToFilter($from, $to)
    {
        $transactionCollection = $this->collection->create()->addFieldToFilter('created_at', ['gteq' => $from])
            ->addFieldToSelect([])
            ->addFieldToFilter('created_at', ['lteq' => $to]);

        $storeId = $this->_request->getParam('store', 0);
        if ($storeId) {
            $transactionCollection->addFieldToFilter('store_id', $storeId);
        }

        return $transactionCollection;
    }

    /**
     * @return array
     */
    public function getMpEarnedFields()
    {
        return [
            ReportsHelper::ACTION_ADMIN,
            ReportsHelper::ACTION_EARNING_ORDER,
            ReportsHelper::ACTION_SIGN_UP,
            ReportsHelper::ACTION_NEWSLETTER,
            ReportsHelper::ACTION_REVIEW_PRODUCT,
            ReportsHelper::ACTION_CUSTOMER_BIRTHDAY,
            ReportsHelper::ACTION_CUSTOMER_COMEBACK,
            ReportsHelper::ACTION_LIKE_FACEBOOK,
            ReportsHelper::ACTION_SHARE_FACEBOOK,
            ReportsHelper::ACTION_TWEET_TWITTER,
            ReportsHelper::ACTION_REFERRAL_EARNING
        ];
    }
}
