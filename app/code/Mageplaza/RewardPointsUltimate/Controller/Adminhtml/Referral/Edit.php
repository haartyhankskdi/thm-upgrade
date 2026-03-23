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

namespace Mageplaza\RewardPointsUltimate\Controller\Adminhtml\Referral;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\RewardPointsUltimate\Model\ReferralFactory;

/**
 * Class Edit
 * @package Mageplaza\RewardPointsUltimate\Controller\Adminhtml\Referral
 */
class Edit extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var ReferralFactory
     */
    protected $referralFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * Edit constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ReferralFactory $referralFactory
     * @param Registry $registry
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ReferralFactory $referralFactory,
        Registry $registry
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->referralFactory = $referralFactory;
        $this->registry = $registry;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|Page|void
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $model = $this->referralFactory->create();
        if ($this->getRequest()->getParam('rule_id')) {
            $model->load($this->getRequest()->getParam('rule_id'));
            if (!$model->getRuleId()) {
                $this->messageManager->addError(__('This rule no longer exists.'));
                $this->_redirect('*/*/');

                return;
            }
        }
        $model->getActions()->setFormName('sales_rule_form');
        $model->getActions()->setJsFormObject(
            $model->getActionsFieldSetId($model->getActions()->getFormName())
        );

        $this->registry->register('refer_rule', $model);
        $resultPage->getConfig()->getTitle()->prepend($model->getId() ? $model->getName() : __('New Refer Rule'));

        return $resultPage;
    }
}
