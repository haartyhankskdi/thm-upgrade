<?php
 
namespace MY\Membership\Model\Carrier;
 
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Psr\Log\LoggerInterface;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
 
class Vipfree extends AbstractCarrier implements CarrierInterface
{
 
    protected $_code = 'vipfree';
 
    protected $rateResultFactory;
 
    protected $rateMethodFactory;

    protected $customerRepository;

    protected $customerSession;
 
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        CustomerRepositoryInterface $customerRepository,
        Session $customerSession,
        array $data = []
    )
    {
        $this->rateResultFactory = $rateResultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->customerRepository = $customerRepository;
        $this->customerSession = $customerSession;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }
 
    public function getAllowedMethods()
    {
        return ['vipfree' => $this->getConfigData('name')];
    }

    public function getCustomer()
    {
        return $this->customerRepository->getById($this->customerSession->getCustomerId());
    }

    public function getGroupId()
    {
        return $this->getCustomer()->getGroupId();
    }
 
    public function isLoggedIn()
    {
        return $this->customerSession->isLoggedIn();
    }

    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $cart = $objectManager->get('\Magento\Checkout\Model\Cart'); 
         
        $subTotal = $cart->getQuote()->getSubtotal();
 
        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->rateResultFactory->create();
 
        /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
        $method = $this->rateMethodFactory->create();
 
        $method->setCarrier('vipfree');
        $method->setCarrierTitle($this->getConfigData('title'));
 
        $method->setMethod('vipfree');
        $method->setMethodTitle($this->getConfigData('name'));
 
        /*you can fetch shipping price from different sources over some APIs, we used price from config.xml - xml node price*/
        $amount = $this->getConfigData('price');
        $shippingPrice = $this->getFinalPriceWithHandlingFee($amount);
        $method->setPrice($shippingPrice);
        $method->setCost($amount);        

        if($this->isLoggedIn()){
            if($this->getGroupId() == 6 && $subTotal >= 75){
                $result->append($method);
                return $result;
            }
        }
    }
}