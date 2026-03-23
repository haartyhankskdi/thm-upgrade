<?php

namespace Ebizmarts\BrippoPayments\Block\Payments;

use Ebizmarts\BrippoPayments\Helper\Logger;
use Ebizmarts\BrippoPayments\Helper\Payments;
use Ebizmarts\BrippoPayments\Helper\RecoverCheckout;
use Ebizmarts\BrippoPayments\Helper\Stock;
use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\ImageFactory;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Model\Order;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Ebizmarts\BrippoPayments\Helper\Data as DataHelper;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class Recover extends Template
{
    /** @var string */
    protected $_template = 'Ebizmarts_BrippoPayments::recover.phtml';

    protected $dataHelper;
    protected $request;
    protected $orderRepository;
    protected $searchCriteriaBuilder;
    protected $priceCurrency;
    protected $stockHelper;
    protected $imageHelperFactory;
    protected $productRepository;
    protected $paymentsHelper;
    protected $logger;
    public $order;

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @param Context $context
     * @param DataHelper $dataHelper
     * @param RequestInterface $request
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param PriceCurrencyInterface $priceCurrency
     * @param ProductRepositoryInterface $productRepository
     * @param ImageFactory $imageHelperFactory
     * @param Stock $stockHelper
     * @param Payments $paymentsHelper
     * @param Logger $logger
     * @param EncryptorInterface $encryptor
     * @param array $data
     */
    public function __construct(
        Context $context,
        DataHelper $dataHelper,
        RequestInterface $request,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        PriceCurrencyInterface $priceCurrency,
        ProductRepositoryInterface $productRepository,
        ImageFactory $imageHelperFactory,
        Stock $stockHelper,
        Payments $paymentsHelper,
        Logger $logger,
        EncryptorInterface $encryptor,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->request = $request;
        $this->dataHelper = $dataHelper;
        $this->productRepository = $productRepository;
        $this->imageHelperFactory = $imageHelperFactory;
        $this->priceCurrency = $priceCurrency;
        $this->paymentsHelper = $paymentsHelper;
        $this->stockHelper = $stockHelper;
        $this->logger = $logger;
        $this->encryptor = $encryptor;

        $orderIncrementId = $this->request->getParam('order');
        if (!empty($orderIncrementId)) {
            $this->order = $this->getOrderByIncrementId($orderIncrementId);
        }

        $paymentIntentId = $this->request->getParam('paymentIntentId');
        if (!empty($paymentIntentId)) {
            $this->order = $this->paymentsHelper->getOrderByPaymentIntentId($paymentIntentId);
        }
    }

    /**
     * @param $incrementId
     * @return OrderInterface|null
     */
    public function getOrderByIncrementId($incrementId)
    {
        if (empty($incrementId)) {
            return null;
        }

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('increment_id', $incrementId, 'eq')
            ->create();
        $orderList = $this->orderRepository->getList($searchCriteria)->getItems();

        if (!empty($orderList) && count($orderList) > 0) {
            return reset($orderList);
        } else {
            return null;
        }
    }

    /**
     * @return bool
     */
    public function hasOrderRecoverableState(): bool
    {
        return $this->order->getState() === Order::STATE_NEW
            || $this->order->getState() === Order::STATE_PENDING_PAYMENT
            || $this->order->getState() === Order::STATE_HOLDED
            || $this->order->getState() === Order::STATE_CANCELED;
    }

    /**
     * @return string
     */
    public function getGrandTotal(): string
    {
        return $this->priceCurrency->format($this->order->getGrandTotal(), false);
    }

    /**
     * @return string
     */
    public function getBillingAddressJsonStringyfied(): string
    {
        return '{'
            . 'email: "' . ($this->order->getBillingAddress()->getEmail() ?? $this->order->getCustomerEmail()) . '",'
            . 'name: "' . $this->order->getBillingAddress()->getFirstname() . ' '
            . $this->order->getBillingAddress()->getLastname() . '",'
            . (!empty($this->order->getBillingAddress()->getTelephone())
                ? 'phone: "' . $this->order->getBillingAddress()->getTelephone() . '",'
                : '')
            . 'address: {'
            . (!empty($this->order->getBillingAddress()->getStreet())
                ? 'line1: "' . $this->order->getBillingAddress()->getStreet()[0] . '",'
                : '')
            . (!empty($this->order->getBillingAddress()->getStreet())
            && count($this->order->getBillingAddress()->getStreet()) > 1
                ? 'line2: "' . $this->order->getBillingAddress()->getStreet()[1] . '",'
                : 'line2: "",')
            . 'city: "' . $this->order->getBillingAddress()->getCity() . '",'
            . (!empty($this->order->getBillingAddress()->getRegion())
                ? 'state: "' . $this->order->getBillingAddress()->getRegion() . '",'
                : '')
            . 'postal_code: "' . $this->order->getBillingAddress()->getPostcode() . '",'
            . 'country: "' . $this->order->getBillingAddress()->getCountryId() . '"'
            . '}}';
    }

    public function getProductImageUrl($productId): string
    {
        try {
            $product = $this->productRepository->getById($productId);
            $imageHelper = $this->imageHelperFactory->create();
            return $imageHelper->init($product, 'product_thumbnail_image')->getUrl();
        } catch (Exception $e) {
            return '';
        }
    }

    /**
     * @return bool
     */
    public function isStockAvailable(): bool
    {
        try {
            foreach ($this->order->getAllVisibleItems() as $item) {
                $sku = $item->getSku();
                $quantity = (int)$item->getQtyOrdered();
                if (!$this->stockHelper->isStockAvailable($sku, $quantity)) {
                    return false;
                }
            }
            return true;
        } catch (Exception $ex) {
            return false;
        }
    }

    /**
     * @return mixed|null
     */
    public function getLinkSource()
    {
        $source = $this->request->getParam('source');
        if (!empty($source)) {
            return $source;
        }
        return '';
    }

    /**
     * @return bool
     */
    public function isManual(): bool
    {
        return !empty($this->request->getParam('manual'));
    }

    /**
     * @return bool
     */
    public function isSoftFailRecovery(): bool
    {
        return !empty($this->request->getParam('softf'));
    }

    /**
     * @return int
     */
    public function getNotification(): int
    {
        $notifParam = $this->request->getParam('notif');
        return $notifParam === null ? -1 : intval($notifParam);
    }

    /**
     * @return bool
     */
    public function isRecoverSignatureValid(): bool {
        $recoverSignature = $this->getRequest()->getParam('sig');
        if (empty($recoverSignature)) {
            return false;
        }
        $orderIncrementId = $this->request->getParam('order');
        $expected = hash_hmac('sha256', $orderIncrementId, $this->encryptor->getHash(RecoverCheckout::RECOVER_HASH));

        return hash_equals($expected, $recoverSignature);
    }
}
