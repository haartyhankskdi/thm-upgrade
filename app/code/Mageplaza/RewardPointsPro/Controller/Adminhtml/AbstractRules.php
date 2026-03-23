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

namespace Mageplaza\RewardPointsPro\Controller\Adminhtml;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\RewardPointsPro\Model\CatalogRule;
use Mageplaza\RewardPointsPro\Model\CatalogRuleFactory;
use Mageplaza\RewardPointsPro\Model\ShoppingCartEarningRuleFactory;
use Mageplaza\RewardPointsPro\Model\ShoppingCartSpendingRuleFactory;
use Mageplaza\RewardPointsPro\Model\Source\ShoppingCart\DiscountStyle;
use Mageplaza\RewardPointsPro\Model\Source\ShoppingCart\Type;

/**
 * Class AbstractRules
 * @package Mageplaza\RewardPointsPro\Controller\Adminhtml
 */
abstract class AbstractRules extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var $catalogRuleFactory
     */
    protected $catalogRuleFactory;

    /**
     * @var ShoppingCartEarningRuleFactory
     */
    protected $shoppingCartEarningRuleFactory;

    /**
     * @var ShoppingCartSpendingRuleFactory
     */
    protected $shoppingCartSpendingRuleFactory;

    /**
     * AbstractRules constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $registry
     * @param CatalogRuleFactory $catalogRuleFactory
     * @param ShoppingCartEarningRuleFactory $shoppingCartEarningRuleFactory
     * @param ShoppingCartSpendingRuleFactory $shoppingCartSpendingRuleFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Registry $registry,
        CatalogRuleFactory $catalogRuleFactory,
        ShoppingCartEarningRuleFactory $shoppingCartEarningRuleFactory,
        ShoppingCartSpendingRuleFactory $shoppingCartSpendingRuleFactory
    ) {
        $this->catalogRuleFactory = $catalogRuleFactory;
        $this->shoppingCartSpendingRuleFactory = $shoppingCartSpendingRuleFactory;
        $this->shoppingCartEarningRuleFactory = $shoppingCartEarningRuleFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->registry = $registry;

        parent::__construct($context);
    }

    /**
     * Init layout, menu and breadcrumb
     *
     * @return Page
     */
    protected function _initAction()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu(self::ADMIN_RESOURCE);
        $resultPage->addBreadcrumb(__('Reward Points'), __('Reward Points'));

        return $resultPage;
    }

    /**
     * @return CatalogRule|
     * \Mageplaza\RewardPointsPro\Model\ShoppingCartEarningRule|
     * \Mageplaza\RewardPointsPro\Model\ShoppingCartSpendingRule
     */
    public function getRuleModel()
    {
        $controllerName = $this->getRequest()->getControllerName();
        if ($controllerName === 'earning_catalog') {
            $ruleModel = $this->catalogRuleFactory->create();
        } elseif ($controllerName === 'earning_shoppingcart') {
            $ruleModel = $this->shoppingCartEarningRuleFactory->create();
        } else {
            $ruleModel = $this->shoppingCartSpendingRuleFactory->create();
        }

        return $ruleModel;
    }

    /**
     * Delete catalog, shopping cart earning and spending rule
     */
    public function deleteRule()
    {
        $id = $this->getRequest()->getParam('rule_id');
        if ($id) {
            $shoppingCartRule = $this->getRuleModel();
            $shoppingCartRule->load($id);
            if ($id != $shoppingCartRule->getRuleId()) {
                $this->messageManager->addErrorMessage(__('This rule no longer exists.'));
                $this->_redirect('*/*/');

                return;
            }
            try {
                $shoppingCartRule->delete();
                $this->messageManager->addSuccessMessage(__('The rule has been deleted.'));
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Something went wrong while delete the rule data.')
                );
                $this->_redirect('*/*/edit/', ['rule_id' => $shoppingCartRule->getRuleId()]);

                return;
            }
        }

        $this->_redirect('*/*/');
    }

    /**
     * Save shopping cart earning and spending rule
     */
    public function saveShoppingCartRule()
    {
        if ($this->getRequest()->getPostValue()) {
            $data = $this->getRequest()->getParam('rule');
            $id = isset($data['rule_id']) ? $data['rule_id'] : '';
            try {
                $shoppingCartRule = $this->getRuleModel();
                if ($id) {
                    $shoppingCartRule = $shoppingCartRule->load($id);
                    if ($id != $shoppingCartRule->getId()) {
                        throw new LocalizedException(__('The wrong rule is specified.'));
                    }
                }
                $storeLabels = $this->getRequest()->getParam('store_labels');
                if ($storeLabels && is_array($storeLabels)) {
                    $data['store_labels'] = $storeLabels;
                }
                $validateData = new DataObject($data);
                $validateResult = $shoppingCartRule->validateData($validateData);
                if ((int)$validateResult === 1
                    && (int)$validateData->getRuleType() === Type::SHOPPING_CART_SPENDING
                    && $validateData->getDiscountStyle() === DiscountStyle::TYPE_PERCENT
                    && $validateData->getDiscountAmount() > 100) {
                    $validateResult = [];
                    $validateResult[] = __('Please fill discount amount equals or less than 100');
                }

                if ($validateResult !== true) {
                    foreach ($validateResult as $errorMessage) {
                        $this->messageManager->addError($errorMessage);
                    }
                    if ($shoppingCartRule->getId()) {
                        $this->_redirect('*/*/edit/', ['rule_id' => $shoppingCartRule->getRuleId()]);
                    } else {
                        $this->_redirect('*/*/');
                    }

                    return;
                }
                $shoppingCartRule->loadPost($data);
                $shoppingCartRule->addData($data)->save();
                $this->messageManager->addSuccessMessage(__('You saved the rule.'));
                if ($this->getRequest()->getParam('back') && $this->getRequest()->getParam('back') === 'edit') {
                    $this->_redirect('*/*/edit/', ['rule_id' => $shoppingCartRule->getRuleId()]);

                    return;
                }
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage(
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
