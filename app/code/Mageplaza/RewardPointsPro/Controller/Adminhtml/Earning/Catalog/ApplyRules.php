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
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Mageplaza\RewardPointsPro\Controller\Adminhtml\Earning\Catalog;
use Mageplaza\RewardPointsPro\Model\Flag;
use Mageplaza\RewardPointsPro\Model\Job;
use Psr\Log\LoggerInterface;

/**
 * Class ApplyRules
 * @package Mageplaza\RewardPointsPro\Controller\Adminhtml\Earning\Catalog
 */
class ApplyRules extends Catalog
{
    /**
     * Apply all active catalog price rules
     *
     * @return Redirect
     */
    public function execute()
    {
        $errorMessage = __('We can\'t apply the rules.');
        try {
            /** @var Job $ruleJob */
            $ruleJob = $this->_objectManager->get(Job::class);
            $ruleJob->applyAll();

            if ($ruleJob->hasSuccess()) {
                $this->messageManager->addSuccess($ruleJob->getSuccess());
                $this->_objectManager->create(Flag::class)->loadSelf()->setState(0)->save();
            } elseif ($ruleJob->hasError()) {
                $this->messageManager->addError($errorMessage . ' ' . $ruleJob->getError());
            }
        } catch (Exception $e) {
            $this->_objectManager->create(LoggerInterface::class)->critical($e);
            $this->messageManager->addError($errorMessage);
        }

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('*/*/');
    }
}
