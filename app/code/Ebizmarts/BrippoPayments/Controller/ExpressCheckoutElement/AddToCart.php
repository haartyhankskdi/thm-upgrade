<?php

namespace Ebizmarts\BrippoPayments\Controller\ExpressCheckoutElement;

use Ebizmarts\BrippoPayments\Helper\ExpressCheckoutElement;
use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Ebizmarts\BrippoPayments\Helper\Logger;
use Ebizmarts\BrippoPayments\Helper\Data as DataHelper;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Locale\Resolver;
use Magento\Store\Model\StoreManagerInterface;

class AddToCart extends Action
{
    protected $jsonFactory;
    protected $logger;
    protected $checkoutSession;
    protected $dataHelper;
    protected $localeResolver;
    protected $storeManager;
    protected $productRepository;
    protected $eceHelper;

    /**
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param Logger $logger
     * @param DataHelper $dataHelper
     * @param CheckoutSession $checkoutSession
     * @param Resolver $localeResolver
     * @param StoreManagerInterface $storeManager
     * @param ProductRepositoryInterface $productRepository
     * @param ExpressCheckoutElement $eceHelper
     */
    public function __construct(
        Context                    $context,
        JsonFactory                $jsonFactory,
        Logger                     $logger,
        DataHelper                 $dataHelper,
        CheckoutSession            $checkoutSession,
        Resolver                   $localeResolver,
        StoreManagerInterface      $storeManager,
        ProductRepositoryInterface $productRepository,
        ExpressCheckoutElement     $eceHelper
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->logger = $logger;
        $this->dataHelper = $dataHelper;
        $this->checkoutSession = $checkoutSession;
        $this->localeResolver = $localeResolver;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->eceHelper = $eceHelper;
    }

    public function execute()
    {
        try {
            $this->eceHelper->setParamsFromRequestBody($this->getRequest());
            $params = [];
            // @codingStandardsIgnoreStart
            parse_str($this->getRequest()->getParam('request'), $params);
            // @codingStandardsIgnoreEnd
            $shippingAddress = $this->getRequest()->getParam('shippingAddress');

            $quote = $this->checkoutSession->getQuote();
            $scopeId = $this->storeManager->getStore()->getId();
            $currency = $this->eceHelper->getModalCurrencyFromQuote($quote, $scopeId);

            $quote = $this->eceHelper->setUpProductPageQuote(
                $quote,
                $params,
                $scopeId
            );

            if (!empty($shippingAddress)) {
                $this->eceHelper->setShippingAddressToQuoteFromPaymentRequestData(
                    $quote,
                    $shippingAddress,
                    $quote->getQuoteCurrencyCode()
                );
            } else {
                $quote->collectTotals()->save();
            }

            $response = [
                'valid' => 1,
                'options' => $this->eceHelper->getCartOptions($quote, $currency, $scopeId)
            ];
        } catch (Exception $ex) {
            $this->logger->log($ex->getMessage());
            $response = [
                'valid' => 0,
                'message' => $ex->getMessage()
            ];
        }

        $resultJson = $this->jsonFactory->create();
        $resultJson->setData($response);
        return $resultJson;
    }
}
