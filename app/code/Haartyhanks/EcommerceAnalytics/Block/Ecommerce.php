<?php


namespace Haartyhanks\EcommerceAnalytics\Block;

class Ecommerce extends \Magento\Framework\View\Element\Template
{

    protected $orderFactory;
    protected $transactions;
    protected $checkoutSession;
    protected $orderRepository;
    /**
     * Constructor - with dependency
     *
     * @param \Magento\Framework\View\Element\Template\Context  $context
     * @param \Magento\Sales\Model\OrderFactory $orderFactory,
     * @param \Magento\Sales\Api\Data\TransactionSearchResultInterfaceFactory $transactions,
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Api\Data\TransactionSearchResultInterfaceFactory $transactions,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        $this->transactions = $transactions;
        $this->checkoutSession = $checkoutSession;
        $this->_orderFactory = $orderFactory;
        $this->orderRepository = $orderRepository;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context, $data);
    }

    public function getLastOrderId()
    {
        //  return $this->checkoutSession->getLastOrderId();
        // return $_SESSION['checkout']['last_order_id'];
        return isset($_SESSION['checkout']['last_order_id'])?$_SESSION['checkout']['last_order_id']:'';
    }

    public function getStoreName()
    {
        return $this->_storeManager->getStore()->getName();
    }

    /**
     * @return string
     */
    /* public function getTrackingNo($id = 0)
    {
        if(empty($id) || $id == 0){
           return null;
       }
        $transactions = $this->transactions->create()->addOrderIdFilter($id);
        return $transactions->getFirstItem()->getData();
    } */

    /**
     * @return array
     */
    /* public function getTrackingData($id = 0)
    {
        if(empty($id) || $id == 0){
            return null;
       }
        $transactions = $this->transactions->create()->addOrderIdFilter($id);
        return $transactions->getItems();
    } */

    /**
     * @return string
     */
    public function getOrderData($id = 0)
    {
        if(empty($id) || $id == 0){
            return null;
       }
        $order = $this->_orderFactory->create()->load($id);
        return $order->getItemsCollection();
    }

    /* 
        USE of repository
    */
    public function getOrderById($id = 0) {
       if(empty($id) || $id == 0){
            return null;
       }
    //    return $id;
        return $this->orderRepository->get($id);
    }

    /* 
        Get Analitic code
    */

    public function getAnaliticCode()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue('google/analytics/account', $storeScope);
    }
}
