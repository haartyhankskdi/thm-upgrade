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
use Mageplaza\RewardPointsUltimate\Model\BehaviorFactory;

/**
 * Class Delete
 * @package Mageplaza\RewardPointsUltimate\Controller\Adminhtml\Earning\Behavior
 */
class Delete extends Action
{
    /**
     * @var BehaviorFactory
     */
    protected $behaviorFactory;

    /**
     * Delete constructor.
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
        $id = $this->getRequest()->getParam('rule_id');
        if ($id) {
            $behavior = $this->behaviorFactory->create();
            $behavior->load($id);
            if ($behavior->getId() != $id) {
                $this->messageManager->addError(__('This rule no longer exists.'));
                $this->_redirect('*/*/');

                return;
            }
            try {
                $behavior->delete();
                $this->messageManager->addSuccess(__('The rule has been deleted.'));
            } catch (Exception $e) {
                $this->messageManager->addError(
                    __('Something went wrong while deleting the rule data.')
                );
                $this->_redirect('*/*/edit/', ['rule_id' => $behavior->getId()]);

                return;
            }
        }

        $this->_redirect('*/*/');
    }
}
