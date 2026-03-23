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

namespace Mageplaza\RewardPointsUltimate\Block\Catalog;

use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Mageplaza\RewardPointsUltimate\Helper\Data as HelperData;
use Mageplaza\RewardPointsUltimate\Model\Behavior;
use Mageplaza\RewardPointsUltimate\Model\BehaviorFactory;
use Mageplaza\RewardPointsUltimate\Model\Source\CustomerEvents;
use Mageplaza\RewardPointsUltimate\Helper\Data;

/**
 * Class Review
 * @package Mageplaza\RewardPointsUltimate\Block\Catalog
 */
class Review extends Template
{
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var BehaviorFactory
     */
    protected $behaviorFactory;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * Review constructor.
     *
     * @param Context $context
     * @param BehaviorFactory $behaviorFactory
     * @param Session $customerSession
     * @param Data $helperData
     * @param array $data
     */
    public function __construct(
        Context $context,
        BehaviorFactory $behaviorFactory,
        Session $customerSession,
        Data $helperData,
        array $data = []
    ) {
        $this->behaviorFactory = $behaviorFactory;
        $this->customerSession = $customerSession;
        $this->helperData      = $helperData;

        parent::__construct($context, $data);
    }

    /**
     * @return bool
     */
    public function CheckValid()
    {
        $customerId = $this->customerSession->getCustomerId();
        /** @var Behavior $behavior */
        $behavior = $this->behaviorFactory->create()
            ->getBehaviorRuleByAction(CustomerEvents::PRODUCT_REVIEW, true);
        if ($behavior->getMaxPoint() > 0) {
            $reviewPointsEarned = $behavior->checkMaxPoint(
                HelperData::ACTION_REVIEW_PRODUCT,
                $customerId
            );
            if ($reviewPointsEarned) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductReviewPointHtml()
    {
        return $this->helperData->getProductReviewPointHtml();
    }
}
