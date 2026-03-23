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

namespace Mageplaza\RewardPointsUltimate\Block\Account;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Mageplaza\RewardPointsUltimate\Model\BehaviorFactory;
use Mageplaza\RewardPointsUltimate\Model\Source\CustomerEvents;

/**
 * Class Subscribe
 * @package Mageplaza\RewardPointsUltimate\Block\Account
 */
class Subscribe extends Template
{
    /**
     * @var BehaviorFactory
     */
    protected $behaviorFactory;

    /**
     * Subscribe constructor.
     *
     * @param Context $context
     * @param BehaviorFactory $behaviorFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        BehaviorFactory $behaviorFactory,
        array $data = []
    ) {
        $this->behaviorFactory = $behaviorFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return mixed
     */
    public function getPointNewsletter()
    {
        return $this->behaviorFactory->create()->getPointByAction(CustomerEvents::NEWSLETTER);
    }
}
