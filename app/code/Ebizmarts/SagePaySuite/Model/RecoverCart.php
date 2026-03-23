<?php

namespace Ebizmarts\SagePaySuite\Model;

use Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger;
use Ebizmarts\SagePaySuite\Model\ObjectLoader\OrderLoader;
use Ebizmarts\SagePaySuite\Model\SessionInterface as SagePaySession;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\AddressInterfaceFactory;
use Magento\Quote\Model\Quote\Address;
use Magento\Framework\Event\Manager as EventManager;

class RecoverCart
{
    public const ORDER_ERROR_MESSAGE   = "Order not available";
    public const QUOTE_ERROR_MESSAGE   = "Quote not available";
    private const GENERAL_ERROR_MESSAGE = "Not possible to recover quote";
    private const INCREMENT_NOT_AVAILABLE = "Increment not available";

    /** @var Session */
    private $checkoutSession;

    /** @var Logger */
    private $suiteLogger;

    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /** @var QuoteFactory */
    private $quoteFactory;

    /** @var CartRepositoryInterface */
    private $quoteRepository;

    /** @var ManagerInterface */
    private $messageManager;

    /** @var bool */
    private $_shouldCancelOrders;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var OrderLoader */
    private $orderLoader;

    /** @var int */
    private $quoteId;

    /** @var AddressInterfaceFactory */
    private $addressFactory;

    /** @var EventManager $eventManager */
    private $eventManager;

    /**
     * RecoverCart constructor.
     * @param Session $checkoutSession
     * @param Logger $suiteLogger
     * @param OrderRepositoryInterface $orderRepository
     * @param QuoteFactory $quoteFactory
     * @param CartRepositoryInterface $quoteRepository
     * @param ManagerInterface $messageManager
     * @param ProductRepositoryInterface $productRepository
     * @param OrderLoader $orderLoader
     * @param AddressInterfaceFactory $addressFactory
     * @param EventManager $eventManager
     */
    public function __construct(
        Session $checkoutSession,
        Logger $suiteLogger,
        OrderRepositoryInterface $orderRepository,
        QuoteFactory $quoteFactory,
        CartRepositoryInterface $quoteRepository,
        ManagerInterface $messageManager,
        ProductRepositoryInterface $productRepository,
        OrderLoader $orderLoader,
        AddressInterfaceFactory $addressFactory,
        EventManager $eventManager
    ) {
        $this->checkoutSession   = $checkoutSession;
        $this->suiteLogger       = $suiteLogger;
        $this->orderRepository   = $orderRepository;
        $this->quoteFactory      = $quoteFactory;
        $this->quoteRepository   = $quoteRepository;
        $this->messageManager    = $messageManager;
        $this->productRepository = $productRepository;
        $this->orderLoader       = $orderLoader;
        $this->addressFactory    = $addressFactory;
        $this->eventManager      = $eventManager;
    }

    /**
     * If orderIds = null then get presaved order id from session
     * @param array|int|null $orderIds
     */
    public function execute($orderIds = null)
    {
        $orderIds = (($orderIds === null) || is_array($orderIds)) ? $orderIds : (int)$orderIds;

        $orders = $this->getOrders($orderIds);

        if ($this->verifyIfOrdersAreValid($orders)) {
            if ($this->getQuoteId() !== null) {
                if ($this->_shouldCancelOrders) {
                    $this->tryCancelOrders($orders);
                }
                try {
                    $this->cloneQuoteAndReplaceInSession();
                } catch (LocalizedException $e) {
                    $this->logExceptionAndShowError(self::GENERAL_ERROR_MESSAGE, $e);
                }
            } else {
                $this->addError(self::QUOTE_ERROR_MESSAGE);
            }
        } else {
            $this->addError(self::ORDER_ERROR_MESSAGE);
        }
    }

    /**
     * @param array $orderIds
     * @return array|null
     */
    private function getOrders($orderIds)
    {
        if (is_int($orderIds)) {
            $orderIds = $this->_formatOrderIdArray($orderIds);
        }
        $orders = [];

        if (empty($orderIds)) {
            $orderIds = [$this->getOrderIdFromSession()];
            $orderIds = $this->_formatOrderIdsArray($orderIds);
        }

        if (!empty($orderIds)) {
            foreach ($orderIds as $orderId => $incrementId) {
                if ($orderId != null) {
                    $order = $this->orderLoader->loadOrderById($orderId);
                    if ($order->getQuoteId() != null) {
                        $this->setQuoteId($order->getQuoteId());
                    }
                    $orders[] = $order;
                }
            }
        }

        return $orders;
    }

    /**
     * @param int $orderId
     * @return array
     */
    private function _formatOrderIdArray($orderId)
    {
        return [$orderId => self::INCREMENT_NOT_AVAILABLE];
    }

    /**
     * @param array $orderIds
     * @return array
     */
    private function _formatOrderIdsArray($orderIds)
    {
        $data = [];
        foreach ($orderIds as $orderId) {
            $data = [$orderId => self::INCREMENT_NOT_AVAILABLE];
        }
        return $data;
    }

    /**
     * @return int
     */
    public function getOrderIdFromSession()
    {
        return $this->checkoutSession->getData(SagePaySession::PRESAVED_PENDING_ORDER_KEY);
    }

    /**
     * @param array $orders
     * @return bool
     */
    private function verifyIfOrdersAreValid($orders)
    {
        $ordersAreValid = false;

        foreach ($orders as $order) {
            if ($order !== null && $order->getId() !== null) {
                $ordersAreValid = true;
            } else {
                $ordersAreValid = false;
            }
        }

        return $ordersAreValid;
    }

    /**
     * @param array $orders
     */
    private function tryCancelOrders($orders)
    {
        try {
            foreach ($orders as $order) {
                $state = $order->getState();
                if ($state === Order::STATE_PENDING_PAYMENT) {
                    $order->cancel()->save();
                } elseif ($state !== Order::STATE_CANCELED) {
                    $this->suiteLogger->sageLog(Logger::LOG_REQUEST, "Incorrect state found on order " .
                    $order->getIncrementId() . " when trying to cancel it. State found: " .
                    $state, [__METHOD__, __LINE__]);
                }
            }
        } catch (\Exception $e) {
            $this->suiteLogger->logException($e, [__METHOD__, __LINE__]);
        }
    }

    /**
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function cloneQuoteAndReplaceInSession()
    {
        $quoteId = $this->getQuoteId();
        $quote = $this->quoteRepository->get($quoteId);
        $items = $quote->getAllVisibleItems();
        $customer = $quote->getCustomer();

        $newQuote = $this->quoteFactory->create();
        $newQuote->setStoreId($quote->getStoreId());
        $newQuote->setIsActive(1);
        $newQuote->setReservedOrderId(null);
        $newQuote->setCustomer($customer);
        $newQuote = $this->cloneCustomerData($quote, $newQuote);

        foreach ($items as $item) {
            try {
                $product = $this->productRepository->getById($item->getProductId(), false, $quote->getStoreId(), true);
                $request = $item->getBuyRequest();

                $newQuote->addProduct($product, $request);
            } catch (NoSuchEntityException $e) {
                $this->suiteLogger->sageLog(Logger::LOG_EXCEPTION, $e->getMessage(), [__METHOD__, __LINE__]);
                $this->suiteLogger->sageLog(Logger::LOG_EXCEPTION, $e->getTraceAsString(), [__METHOD__, __LINE__]);
            }
        }

        // We need save new quote before dispatch observers, so we have newQuoteId
        $this->quoteRepository->save($newQuote);
        $this->eventManager->dispatch(
            'sage_pay_suite_after_add_products_to_new_quote',
            [
                'quote' => $newQuote
            ]
        );
        $this->eventManager->dispatch(
            'sage_pay_suite_recover_coupon',
            [
                'quote' => $quote,
                'newQuote' => $newQuote
            ]
        );
        // Reload quote in case observers added attributes.
        $this->quoteRepository->save($newQuote);
        $newQuote = $this->quoteRepository->get($newQuote->getId());
        $shippingAddress = $newQuote->getShippingAddress();
        $shippingAddress->unsetData('cached_items_all');
        $newQuote->collectTotals();
        $this->quoteRepository->save($newQuote);
        $newQuote->getShippingAddress()->collectShippingRates();

        $this->checkoutSession->replaceQuote($newQuote);
    }

    private function removeFlag()
    {
        $this->checkoutSession->setData(SagePaySession::PRESAVED_PENDING_ORDER_KEY, null);
        $this->checkoutSession->setData(SagePaySession::CONVERTING_QUOTE_TO_ORDER, 0);
    }

    /**
     * @param $message
     */
    private function addError($message)
    {
        $this->removeFlag();
        $this->messageManager->addErrorMessage(__($message));
    }

    /**
     * @param $message
     * @param $exception
     */
    private function logExceptionAndShowError($message, $exception)
    {
        $this->addError($message);
        $this->suiteLogger->sageLog(Logger::LOG_EXCEPTION, $exception->getMessage());
        $this->suiteLogger->sageLog(Logger::LOG_EXCEPTION, $exception->getTraceAsString(), [__METHOD__, __LINE__]);
    }

    /**
     * @param bool $shouldCancelOrder
     * @return $this
     */
    public function setShouldCancelOrders(bool $shouldCancelOrder)
    {
        $this->_shouldCancelOrders = $shouldCancelOrder;
        return $this;
    }

    /**
     * @return int
     */
    public function getQuoteId()
    {
        return $this->quoteId;
    }

    /**
     * @param int $quoteId
     */
    public function setQuoteId($quoteId)
    {
        $this->quoteId = $quoteId;
    }

    /**
     * @param CartInterface $quote
     * @param CartInterface $newQuote
     * @return CartInterface
     */
    private function cloneCustomerData($quote, $newQuote)
    {
        $newQuote->setCustomerEmail($quote->getCustomerEmail());
        $newQuote->setCustomerFirstname($quote->getCustomerFirstname());
        $newQuote->setCustomerLastname($quote->getCustomerLastname());
        $newQuote->setCustomerPrefix($quote->getCustomerPrefix());
        $newQuote->setCustomerMiddlename($quote->getCustomerMiddlename());
        $shippingAddressInterface = $this->cloneAddressData($quote->getShippingAddress());
        $newQuote->setShippingAddress($shippingAddressInterface);
        if (!$shippingAddressInterface->getSameAsBilling()) {
            $billingAddressInterface = $this->cloneAddressData($quote->getBillingAddress(), false);
            $newQuote->setBillingAddress($billingAddressInterface);
        } else {
            $newQuote->setBillingAddress($shippingAddressInterface);
        }
        $newQuote->setCustomerIsGuest($quote->getCustomerIsGuest());
        $newQuote->setCustomerGroupId($quote->getCustomerGroupId());

        return $newQuote;
    }

    /**
     * @param Address|AddressInterface $quoteAddress
     * @return Address
     */
    private function cloneAddressData($quoteAddress, $isShippingAddress = true)
    {
        /** @var Address $newAddress */
        $newAddress = $this->addressFactory->create();
        $newAddress->setEmail($quoteAddress->getEmail());
        $newAddress->setCountryId($quoteAddress->getCountryId());
        $newAddress->setRegionId($quoteAddress->getRegionId());
        $newAddress->setRegionCode($quoteAddress->getRegionCode());
        $newAddress->setRegion($quoteAddress->getRegion());
        $newAddress->setCustomerId($quoteAddress->getCustomerId());
        $newAddress->setStreet($quoteAddress->getStreet());
        $newAddress->setCompany($quoteAddress->getCompany());
        $newAddress->setTelephone($quoteAddress->getTelephone());
        $newAddress->setPostcode($quoteAddress->getPostcode());
        $newAddress->setCity($quoteAddress->getCity());
        $newAddress->setFirstname($quoteAddress->getFirstname());
        $newAddress->setLastname($quoteAddress->getLastname());
        $newAddress->setVatId($quoteAddress->getVatId());
        $newAddress->setCustomerAddressId($quoteAddress->getCustomerAddressId());
        $newAddress->setSaveInAddressBook($quoteAddress->getSaveInAddressBook());
        if ($isShippingAddress) {
            $newAddress->setSameAsBilling($quoteAddress->getSameAsBilling());
            $newAddress->setShippingMethod($quoteAddress->getShippingMethod());
            $newAddress->setShippingAmount($quoteAddress->getShippingAmount());
            $newAddress->setShippingTaxAmount($quoteAddress->getShippingTaxAmount());
            $newAddress->setShippingInclTax($quoteAddress->getShippingInclTax());
            $newAddress->setShippingDescription($quoteAddress->getShippingDescription());
            $newAddress->setFreeShipping($quoteAddress->getFreeShipping());
            $newAddress->setBaseShippingInclTax($quoteAddress->getBaseShippingInclTax());
            $newAddress->setBaseShippingTaxAmount($quoteAddress->getBaseShippingTaxAmount());
            $newAddress->setBaseShippingAmount($quoteAddress->getBaseShippingAmount());
            $newAddress->setCollectShippingRates(true);
        }

        return $newAddress;
    }
}
