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

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Mageplaza\RewardPointsUltimate\Controller\Adminhtml\AbstractMilestone;
use Mageplaza\RewardPointsUltimate\Model\Milestone;

/**
 * Class Edit
 * @package Mageplaza\RewardPointsUltimate\Controller\Adminhtml\Milestone
 */
class Edit extends AbstractMilestone
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page|ResponseInterface|Redirect|ResultInterface|Page
     */
    public function execute()
    {
        /** @var Milestone $tier */
        $tier = $this->_initTier();

        if (!$tier) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*');

            return $resultRedirect;
        }

        $data = $this->_session->getData('mageplaza_rw_milestone_tier_data', true);
        if (!empty($data)) {
            $tier->setData($data);
        }

        /** @var \Magento\Backend\Model\View\Result\Page|Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Mageplaza_RewardPoints::milestone');
        $resultPage->getConfig()->getTitle()->set(__('Manage Milestone Tier'));

        $title = $tier->getId() ? __('Edit "%1"', $tier->getName()) : __('New Milestone Tier');
        $resultPage->getConfig()->getTitle()->prepend($title);

        return $resultPage;
    }
}
