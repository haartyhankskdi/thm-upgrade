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

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\RewardPointsUltimate\Model\ReferralFactory;

/**
 * Class Save
 * @package Mageplaza\RewardPointsUltimate\Controller\Adminhtml\Referral
 */
class Save extends Action
{
    /**
     * @var ReferralFactory
     */
    protected $referralFactory;

    /**
     * Save constructor.
     *
     * @param Context $context
     * @param ReferralFactory $referralFactory
     */
    public function __construct(
        Context $context,
        ReferralFactory $referralFactory
    ) {
        $this->referralFactory = $referralFactory;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        if ($this->getRequest()->getPostValue()) {
            $data = $this->getRequest()->getParam('rule');
            $id = isset($data['rule_id']) ? $data['rule_id'] : '';
            try {
                $refer = $this->referralFactory->create();
                if ($id) {
                    $refer->load($id);
                    if ($id != $refer->getId()) {
                        throw new LocalizedException(__('The wrong rule is specified.'));
                    }
                }

                $storeLabels = $this->getRequest()->getParam('store_labels');
                if ($storeLabels && is_array($storeLabels)) {
                    $data['store_labels'] = $storeLabels;
                }
                $validateResult = $refer->validateData(new DataObject($data));

                if ($validateResult !== true) {
                    foreach ($validateResult as $errorMessage) {
                        $this->messageManager->addError($errorMessage);
                    }
                    if ($refer->getId()) {
                        $this->_redirect('*/*/edit/', ['rule_id' => $refer->getRuleId()]);
                    } else {
                        $this->_redirect('*/*/');
                    }

                    return;
                }
                $refer->loadPost($data);
                $refer->addData($data)->save();
                $this->messageManager->addSuccess(__('You saved the rule.'));
                if ($this->getRequest()->getParam('back') && $this->getRequest()->getParam('back') == 'edit') {
                    $this->_redirect('*/*/edit/', ['rule_id' => $refer->getRuleId()]);

                    return;
                }
            } catch (Exception $e) {
                $this->messageManager->addError(__('Something went wrong while saving the rule data.'));
                if (!empty($id)) {
                    $this->_redirect('*/*/edit', ['rule_id' => $id]);
                } else {
                    $this->_redirect('*/*/new');
                }

                return;
            }
        }
        $this->_redirect('*/*/');
    }
}
