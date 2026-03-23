<?php

namespace Ebizmarts\BrippoPayments\Controller\Payments;

use Ebizmarts\BrippoPayments\Helper\Logger;
use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Api\OrderRepositoryInterface;

class Reorder extends Action
{
    protected $orderRepository;
    protected $cartRepository;
    protected $quoteFactory;
    protected $productRepository;
    protected $checkoutSession;
    protected $logger;

    /**
     * @param Context $context
     * @param OrderRepositoryInterface $orderRepository
     * @param CartRepositoryInterface $cartRepository
     * @param QuoteFactory $quoteFactory
     * @param ProductRepositoryInterface $productRepository
     * @param Session $checkoutSession
     * @param Logger $logger
     */
    public function __construct(
        Context $context,
        OrderRepositoryInterface $orderRepository,
        CartRepositoryInterface $cartRepository,
        QuoteFactory $quoteFactory,
        ProductRepositoryInterface $productRepository,
        Session $checkoutSession,
        Logger $logger
    ) {
        parent::__construct($context);
        $this->orderRepository = $orderRepository;
        $this->cartRepository = $cartRepository;
        $this->quoteFactory = $quoteFactory;
        $this->productRepository = $productRepository;
        $this->checkoutSession = $checkoutSession;
        $this->logger = $logger;
    }

    public function execute()
    {
        try {
            $recoverOrderId = $this->getRequest()->getParam('recoverOrderId');
            $order = $this->orderRepository->get($recoverOrderId);
            if (empty($order) || empty($order->getEntityId())) {
                throw new LocalizedException(__('Order not found'));
            }

            // Create a new quote
            $quote = $this->quoteFactory->create();
            $quote->setStoreId($order->getStoreId());

            // Add items to the quote
            foreach ($order->getAllVisibleItems() as $item) {
                $product = $this->productRepository->get($item->getSku());
                try {
                    $quote->addProduct($product, $item->getQtyOrdered());
                } catch (Exception $ex) {
                    continue;
                }
            }

            // Collect totals and save the quote
            $quote->collectTotals();
            $this->cartRepository->save($quote);

            // Set the quote as the active quote
            $this->checkoutSession->replaceQuote($quote);
            $this->messageManager->addSuccessMessage(
                __('The order has been restored to your cart with available products only.')
            );
        } catch (Exception $ex) {
            $this->messageManager->addErrorMessage($ex->getMessage());
        }

        return $this->_redirect('checkout/cart');
    }
}
