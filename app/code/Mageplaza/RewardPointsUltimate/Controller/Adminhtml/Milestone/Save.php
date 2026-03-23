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
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\Core\Helper\Media;
use Mageplaza\RewardPointsUltimate\Controller\Adminhtml\AbstractMilestone;
use Mageplaza\RewardPointsUltimate\Helper\Data;
use Mageplaza\RewardPointsUltimate\Model\Milestone;
use Mageplaza\RewardPointsUltimate\Model\MilestoneFactory;

/**
 * Class Save
 * @package Mageplaza\RewardPointsUltimate\Controller\Adminhtml\Milestone
 */
class Save extends AbstractMilestone
{
    /**
     * @var Media
     */
    private $mediaHelper;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * Save constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $registry
     * @param MilestoneFactory $milestoneFactory
     * @param LayoutFactory $resultLayoutFactory
     * @param Media $mediaHelper
     * @param Data $helperData
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Registry $registry,
        MilestoneFactory $milestoneFactory,
        LayoutFactory $resultLayoutFactory,
        Media $mediaHelper,
        Data $helperData
    ) {
        $this->mediaHelper = $mediaHelper;
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

        if ($data = $this->getRequest()->getPost('tier')) {
            /** @var Milestone $tier */
            $tier = $this->_initTier();

            $validate = $this->_prepareData($tier, $data);

            if (!$validate) {
                $this->messageManager->addErrorMessage(__('Already a Milestone Tier with the same parameters you enter.'));
                $resultRedirect->setPath('mprewardultimate/*/');
                return $resultRedirect;
            }

            $this->_eventManager->dispatch(
                'mageplaza_rw_milestone_tier_prepare_save',
                ['tier' => $tier, 'request' => $this->getRequest()]
            );

            try {
                $tier->save();
                $this->helperData->updateTierCustomer();

                $this->messageManager->addSuccessMessage(__('The Milestone Tier has been saved.'));
                $this->_getSession()->setData('mageplaza_rw_milestone_tier_data', false);

                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath('mprewardultimate/*/edit', ['id' => $tier->getId(), '_current' => true]);
                } else {
                    $resultRedirect->setPath('mprewardultimate/*/');
                }

                return $resultRedirect;
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while saving the Milestone Tier.')
                );
            }

            $this->_getSession()->setData('mageplaza_rw_milestone_tier_data', $data);

            $resultRedirect->setPath('mprewardultimate/*/edit', ['id' => $tier->getId(), '_current' => true]);

            return $resultRedirect;
        }

        $resultRedirect->setPath('mprewardultimate/*/');

        return $resultRedirect;
    }

    /**
     * @param Milestone $tier
     * @param array $data
     *
     * @return bool
     */
    protected function _prepareData($tier, $data = [])
    {
        $validate = true;
        $groupIds = $data['customer_group_id'];

        $data['customer_group_ids'] = (isset($data['customer_group_id']) && $data['customer_group_id']) ? implode(
            ',',
            $data['customer_group_id']
        ) : '';

        $data['website_ids'] = (isset($data['website_ids']) && $data['website_ids']) ? implode(
            ',',
            $data['website_ids']
        ) : '';

        if (!$this->getRequest()->getParam('image')) {
            try {
                $this->mediaHelper->uploadImage($data, 'image', 'rewardpoints/tier', $tier->getImage());
            } catch (Exception $exception) {
                $data['image'] = isset($data['image']['value']) ? $data['image']['value'] : '';
            }
        } else {
            $data['image'] = '';
        }

        if ($tier->getId() === '1') {
            unset($data['customer_group_ids']);
            unset($data['website_ids']);
            unset($data['min_point']);
            unset($data['sum_order']);
            unset($data['status']);
        }

        $tier->addData($data);

        if ($tier->checkDuplicatePoint()) {
            foreach ($groupIds as $groupId) {
                if ($tier->checkDuplicateGroup($groupId)) {
                    $validate = false;
                }
            }
        }

        return $validate;
    }
}
