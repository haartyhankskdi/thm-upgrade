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

namespace Mageplaza\RewardPointsUltimate\Block\Socials;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Mageplaza\RewardPointsUltimate\Helper\Data as HelperData;
use Mageplaza\RewardPointsUltimate\Model\Behavior;
use Mageplaza\RewardPointsUltimate\Model\BehaviorFactory;

/**
 * Class Social
 * @package Mageplaza\RewardPointsUltimate\Block\Social
 */
abstract class AbstractSocial extends Template
{
    /**
     * @var string
     */
    protected $type = 'facebook';

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var Behavior
     */
    protected $behaviorFactory;

    /**
     * AbstractSocial constructor.
     *
     * @param Context $context
     * @param HelperData $helperData
     * @param BehaviorFactory $behaviorFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        HelperData $helperData,
        BehaviorFactory $behaviorFactory,
        array $data = []
    ) {
        $this->helperData = $helperData;
        $this->behaviorFactory = $behaviorFactory;

        parent::__construct($context, $data);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrentUrl()
    {
        return $this->helperData->getCurrentUrl();
    }

    /**
     * @param $pages
     *
     * @return bool
     */
    public function checkPageDisplay($pages)
    {
        $pages = explode(',', $pages);

        return in_array($this->_request->getFullActionName(), $pages);
    }

    /**
     * @return mixed
     */
    public function isEnabled()
    {
        return $this->helperData->isEnabledSocialButton($this->type) && $this->helperData->isEnabled() &&
            $this->checkPageDisplay($this->helperData->getSocialPageDisplay($this->type));
    }

    /**
     * @return HelperData
     */
    public function getHelperUltimate()
    {
        return $this->helperData;
    }
}
