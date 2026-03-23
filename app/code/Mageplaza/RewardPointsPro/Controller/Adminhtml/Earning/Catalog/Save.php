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
 * @package     Mageplaza_RewardPointsPro
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsPro\Controller\Adminhtml\Earning\Catalog;

use Exception;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\RewardPointsPro\Controller\Adminhtml\Earning\Catalog;
use Mageplaza\RewardPointsPro\Model\Flag;

/**
 * Class Save
 * @package Mageplaza\RewardPointsPro\Controller\Adminhtml\Earning\Catalog
 */
class Save extends Catalog
{
    /**
     * @return void
     * @var PageFactory
     */
    public function execute()
    {
        if ($this->getRequest()->getPostValue()) {
            try {
                $data = $this->getRequest()->getParam('rule');
                $catalogRule = $this->catalogRuleFactory->create();
                if (isset($data['rule_id'])) {
                    $id = $data['rule_id'];
                    $catalogRule = $catalogRule->load($id);
                    if ($id != $catalogRule->getId()) {
                        throw new LocalizedException(__('The wrong rule is specified.'));
                    }
                }
                $validateResult = $catalogRule->validateData(new DataObject($data));
                if ($validateResult !== true) {
                    foreach ($validateResult as $errorMessage) {
                        $this->messageManager->addError($errorMessage);
                    }
                    if ($catalogRule->getId()) {
                        $this->_redirect('*/*/edit/', ['rule_id' => $catalogRule->getRuleId()]);
                    } else {
                        $this->_redirect('*/*/');
                    }

                    return;
                }
                $catalogRule->loadPost($data);
                $catalogRule->addData($data)->save();
                $this->_objectManager->create(Flag::class)->loadSelf()->setState(0)->save();
                $this->messageManager->addSuccess(__('You saved the rule.'));
                if ($this->getRequest()->getParam('is_apply')) {
                    $this->getRequest()->setParam('rule_id', $catalogRule->getId());
                    $this->_forward('applyRules');
                } else {
                    if ($catalogRule->isRuleBehaviorChanged()) {
                        $this->_objectManager
                            ->create(Flag::class)
                            ->loadSelf()
                            ->setState(1)
                            ->save();
                    }
                    if ($this->getRequest()->getParam('back')) {
                        $this->_redirect('*/*/edit/', ['rule_id' => $catalogRule->getRuleId()]);

                        return;
                    }
                    $this->_redirect('*/*/');
                }

                return;
            } catch (Exception $exception) {
                $this->messageManager->addError(
                    __('Something went wrong while saving the rule data.')
                );
            }
        }
        $this->_redirect('*/*/');
    }
}
