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

namespace Mageplaza\RewardPointsUltimate\Controller\Adminhtml\Milestone;

use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\RewardPointsUltimate\Controller\Adminhtml\AbstractMilestone;
use Mageplaza\RewardPointsUltimate\Helper\Data;
use Mageplaza\RewardPointsUltimate\Model\Milestone;
use Mageplaza\RewardPointsUltimate\Model\MilestoneFactory;

/**
 * Class Delete
 * @package Mageplaza\RewardPointsUltimate\Controller\Adminhtml\Milestone
 */
class Delete extends AbstractMilestone
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * Delete constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $registry
     * @param LayoutFactory $resultLayoutFactory
     * @param MilestoneFactory $milestoneFactory
     * @param Data $helperData
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Registry $registry,
        LayoutFactory $resultLayoutFactory,
        MilestoneFactory $milestoneFactory,
        Data $helperData
    ) {
        $this->helperData = $helperData;
        parent::__construct(
            $context,
            $resultPageFactory,
            $registry,
            $resultLayoutFactory,
            $milestoneFactory
        );
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page|ResponseInterface|Redirect|ResultInterface|Page
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        /** @var Milestone $tier */
        $tier = $this->_initTier(false);
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $tier->delete();
                $this->helperData->updateTierCustomer();

                $this->messageManager->addSuccessMessage(__('The Milestone Tier has been deleted.'));
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $resultRedirect->setPath('mprewardultimate/*/edit', ['id' => $id]);

                return $resultRedirect;
            }
        } else {
            $this->messageManager->addErrorMessage(__('The Milestone Tier to delete was not found.'));
        }

        $resultRedirect->setPath('mprewardultimate/*/');

        return $resultRedirect;
    }
}
