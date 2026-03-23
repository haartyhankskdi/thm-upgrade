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

namespace Mageplaza\RewardPointsUltimate\Controller\Adminhtml\Earning\Behavior;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\RewardPointsUltimate\Model\BehaviorFactory;

/**
 * Class Save
 * @package Mageplaza\RewardPointssultimate\Controller\Adminhtml\Earning\Behavior
 */
class Save extends Action
{
    /**
     * @var BehaviorFactory
     */
    protected $behaviorFactory;

    /**
     * Save constructor.
     *
     * @param Context $context
     * @param BehaviorFactory $behaviorFactory
     */
    public function __construct(
        Context $context,
        BehaviorFactory $behaviorFactory
    ) {
        $this->behaviorFactory = $behaviorFactory;

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
                $behavior = $this->behaviorFactory->create();
                if ($id) {
                    $behavior->load($id);
                    if ($id != $behavior->getId()) {
                        throw new LocalizedException(__('The wrong rule is specified.'));
                    }
                }

                $behavior->addData($data)->save();
                $this->messageManager->addSuccess(__('You saved the rule.'));
                if ($this->getRequest()->getParam('back') && $this->getRequest()->getParam('back') == 'edit') {
                    $this->_redirect('*/*/edit/', ['rule_id' => $behavior->getId()]);

                    return;
                }
            } catch (Exception $e) {
                $this->messageManager->addError(
                    __('Something went wrong while saving the rule data.')
                );
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
