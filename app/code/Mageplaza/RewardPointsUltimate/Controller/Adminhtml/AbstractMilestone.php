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

namespace Mageplaza\RewardPointsUltimate\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\RewardPointsUltimate\Model\Milestone;
use Mageplaza\RewardPointsUltimate\Model\MilestoneFactory;

/**
 * Class AbstractMilestone
 * @package Mageplaza\RewardPointsUltimate\Controller\Adminhtml
 */
abstract class AbstractMilestone extends Action
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Mageplaza_RewardPoints::milestone';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var MilestoneFactory
     */
    protected $milestoneFactory;

    /**
     * @var LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * AbstractMilestone constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $registry
     * @param LayoutFactory $resultLayoutFactory
     * @param MilestoneFactory $milestoneFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Registry $registry,
        LayoutFactory $resultLayoutFactory,
        MilestoneFactory $milestoneFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->registry = $registry;
        $this->milestoneFactory = $milestoneFactory;
        $this->resultLayoutFactory = $resultLayoutFactory;

        parent::__construct($context);
    }

    /**
     * Initialize transaction object
     *
     * @param bool $isRegistry
     *
     * @return mixed
     */
    protected function _initTier($isRegistry = true)
    {
        $tierId = $this->getRequest()->getParam('id', 0);
        /** @var Milestone $transaction */
        $tier = $this->milestoneFactory->create();
        if ($tierId) {
            $tier->load($tierId);
        }
        if ($isRegistry && !$this->registry->registry('mageplaza_rw_milestone_tier')) {
            $this->registry->register('mageplaza_rw_milestone_tier', $tier);
        }

        return $tier;
    }
}
