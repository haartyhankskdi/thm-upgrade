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

namespace Mageplaza\RewardPointsUltimate\Plugin\Sales;

use Exception;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Controller\Order\Reorder;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\OrderRepository;
use Mageplaza\RewardPointsUltimate\Helper\Data as HelperData;
use Mageplaza\RewardPointsUltimate\Helper\SellPoint;

/**
 * Class BeforeReorder
 * @package Mageplaza\RewardPointsUltimate\Plugin\Sales
 */
class BeforeReorder
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var SellPoint
     */
    protected $sellPoint;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * BeforeReorder constructor.
     *
     * @param HelperData $helperData
     * @param RedirectFactory $resultRedirectFactory
     * @param RequestInterface $request
     * @param OrderRepository $orderRepository
     * @param SellPoint $sellPoint
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        HelperData $helperData,
        RedirectFactory $resultRedirectFactory,
        RequestInterface $request,
        OrderRepository $orderRepository,
        SellPoint $sellPoint,
        ManagerInterface $messageManager
    ) {
        $this->helperData            = $helperData;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->request               = $request;
        $this->orderRepository       = $orderRepository;
        $this->sellPoint             = $sellPoint;
        $this->messageManager        = $messageManager;
    }

    /**
     * @param Reorder $subject
     * @param callable $proceed
     *
     * @return Redirect
     */
    public function aroundExecute(Reorder $subject, callable $proceed)
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            if ($this->helperData->isEnabled()) {
                $orderId    = $this->request->getParam('order_id');
                $order      = $this->orderRepository->get($orderId);
                $items      = $order->getItemsCollection();
                $sellPoints = 0;

                /** @var Item $item */
                foreach ($items as $item) {
                    $product = $item->getProduct()->load($item->getProductId());
                    if ($product->getMpRewardSellProduct() > 0) {
                        $sellPoints += $product->getMpRewardSellProduct() * $item->getQtyOrdered();
                    }
                }

                if (!$this->sellPoint->isValid($sellPoints)) {
                    $this->messageManager->addNoticeMessage(__('You haven\'t enough point to add this product!'));
                    return $resultRedirect->setPath('*/*/history');
                }
            }

            return $proceed();
        } catch (Exception $e) {
            $this->messageManager->addNoticeMessage(__('Something went wrong while reordering.'));

            return $resultRedirect->setPath('*/*/history');
        }
    }
}
