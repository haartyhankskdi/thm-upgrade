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

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Mageplaza\RewardPointsPro\Controller\Adminhtml\Earning\Catalog;
use Mageplaza\RewardPointsPro\Model\Flag;

/**
 * Class Index
 * @package Mageplaza\RewardPointsPro\Controller\Adminhtml\Earning\Catalog
 */
class Index extends Catalog
{
    /**
     * @return ResponseInterface|ResultInterface|Page
     */
    public function execute()
    {
        $dirtyRules = $this->_objectManager->create(Flag::class)->loadSelf();
        if (!empty($dirtyRules)) {
            if ($dirtyRules->getState()) {
                $this->messageManager->addNotice(__('We found updated rules that are not applied. Please click "Apply Rules" to update catalog earning rule.'));
            }
        }

        $resultPage = $this->_initAction();
        $resultPage->getConfig()->getTitle()->prepend(__('Catalog Earning Rules'));

        return $resultPage;
    }
}
