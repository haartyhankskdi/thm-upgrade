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

namespace Mageplaza\RewardPointsUltimate\Plugin\Product;

use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Controller\Cart\Add;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\RewardPointsUltimate\Helper\Data as HelperData;
use Mageplaza\RewardPointsUltimate\Helper\SellPoint;
use Psr\Log\LoggerInterface;

/**
 * Class BeforeAdd
 * @package Mageplaza\RewardPointsUltimate\Plugin\Product
 */
class BeforeAdd
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var SellPoint
     */
    protected $sellPoint;

    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * @var RedirectInterface
     */
    protected $redirect;

    /**
     * BeforeAdd constructor.
     *
     * @param HelperData $helperData
     * @param LoggerInterface $logger
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface $storeManager
     * @param ManagerInterface $messageManager
     * @param SellPoint $sellPoint
     * @param ResultFactory $resultFactory
     * @param RedirectInterface $redirect
     */
    public function __construct(
        HelperData $helperData,
        LoggerInterface $logger,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager,
        ManagerInterface $messageManager,
        SellPoint $sellPoint,
        ResultFactory $resultFactory,
        RedirectInterface $redirect
    ) {
        $this->helperData        = $helperData;
        $this->logger            = $logger;
        $this->productRepository = $productRepository;
        $this->storeManager      = $storeManager;
        $this->messageManager    = $messageManager;
        $this->sellPoint         = $sellPoint;
        $this->resultFactory     = $resultFactory;
        $this->redirect          = $redirect;
    }

    /**
     * @param Add $subject
     * @param callable $proceed
     *
     * @return mixed
     */
    public function aroundExecute(Add $subject, callable $proceed)
    {
        try {
            $isProcessExecute = true;
            $productId        = (int) $subject->getRequest()->getParam('product');
            $mpSellProductBy  = (int) $subject->getRequest()->getParam('mp_sell_product_by');
            if ($this->helperData->isEnabled() && $productId && $mpSellProductBy) {
                $storeId = $this->storeManager->getStore()->getId();
                $product = $this->productRepository->getById($productId, false, $storeId);
                if ($product->getMpRewardSellProduct() > 0) {
                    if ($this->helperData->getAccountHelper()->isCustomerLoggedIn()) {
                        $sellPoints = $product->getMpRewardSellProduct() * $subject->getRequest()->getParam('qty', 1);
                        if (!$this->sellPoint->isValid($sellPoints)) {
                            $this->messageManager->addNoticeMessage(__('You haven\'t enough point to add this product!'));
                            $isProcessExecute = false;
                        }
                    } else {
                        $isProcessExecute = false;
                        $this->messageManager->addNoticeMessage(__('Please sign in to add the product!'));
                    }
                }
            }

            if ($isProcessExecute) {
                return $proceed();
            }
        } catch (Exception $e) {
            $this->logger->critical($e);
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $resultRedirect->setUrl($this->redirect->getRefererUrl());

        return $resultRedirect;
    }
}
