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

namespace Mageplaza\RewardPointsUltimate\Block\Socials\Share;

use Magento\Checkout\Block\Onepage\Success;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order\Config;
use Mageplaza\RewardPointsUltimate\Helper\Data;
use Mageplaza\RewardPointsUltimate\Model\BehaviorFactory;
use Mageplaza\RewardPointsUltimate\Model\Source\CustomerEvents;

/**
 * Class Product
 * @package Mageplaza\RewardPointsUltimate\Block\Social\Share
 */
class Product extends Success
{
    /**
     * @var BehaviorFactory
     */
    protected $behaviorFactory;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var string
     */
    protected $appId;

    /**
     * Product constructor.
     *
     * @param Context $context
     * @param Session $checkoutSession
     * @param Config $orderConfig
     * @param HttpContext $httpContext
     * @param BehaviorFactory $behaviorFactory
     * @param Data $helperData
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        Config $orderConfig,
        HttpContext $httpContext,
        BehaviorFactory $behaviorFactory,
        Data $helperData,
        array $data = []
    ) {
        $this->behaviorFactory = $behaviorFactory;
        $this->helperData = $helperData;

        parent::__construct($context, $checkoutSession, $orderConfig, $httpContext, $data);
    }

    /**
     * @return array
     */
    public function getItems()
    {
        $order = $this->_checkoutSession->getLastRealOrder();

        return $order->getAllVisibleItems();
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function getChildBlocks()
    {
        return $this->getLayout()->getChildBlocks($this->getNameInLayout());
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        if (!$this->helperData->isEnabled()) {
            return false;
        }

        return $this->isEnabledFacebookShare() || $this->isEnabledTwitterShare();
    }

    /**
     * @return mixed
     */
    public function isEnabledFacebookShare()
    {
        $behavior = $this->getBehavior()->getBehaviorRuleByAction(CustomerEvents::SHARE_PURCHASE_FACEBOOK);
        if ($behavior->getId()) {
            $this->appId = trim($behavior->getFbAppId());

            return $behavior->getId();
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function isEnabledTwitterShare()
    {
        return $this->getBehavior()->getBehaviorRuleByAction(CustomerEvents::TWEET_PAGE_WITH_TWITTER)->getId();
    }

    /**
     * @return mixed
     */
    public function getBehavior()
    {
        return $this->behaviorFactory->create();
    }

    /**
     * @return string
     */
    public function getAppId()
    {
        return "&appId=$this->appId&autoLogAppEvents=1";
    }
}
