<?php

namespace Ebizmarts\BrippoPayments\Controller\ExpressCheckout;

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
use Ebizmarts\BrippoPayments\Helper\Express as ExpressHelper;

class AddToCart extends Action
{
    protected $jsonFactory;
    protected $logger;
    protected $checkoutSession;
    protected $dataHelper;
    protected $localeResolver;
    protected $storeManager;
    protected $productRepository;
    protected $expressHelper;

    /**
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param Logger $logger
     * @param DataHelper $dataHelper
     * @param CheckoutSession $checkoutSession
     * @param Resolver $localeResolver
     * @param StoreManagerInterface $storeManager
     * @param ProductRepositoryInterface $productRepository
     * @param ExpressHelper $expressHelper
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
        ExpressHelper              $expressHelper
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->logger = $logger;
        $this->dataHelper = $dataHelper;
        $this->checkoutSession = $checkoutSession;
        $this->localeResolver = $localeResolver;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->expressHelper = $expressHelper;
    }

    public function execute()
    {
        $params = [];
        $this->expressHelper->setParamsFromRequestBody($this->getRequest());

        // @codingStandardsIgnoreStart
        parse_str($this->getRequest()->getParam('request'), $params);
        // @codingStandardsIgnoreEnd
        $shippingAddress = $this->getRequest()->getParam('shippingAddress');

        try {
            $quote = $this->checkoutSession->getQuote();
            $scopeId = $this->storeManager->getStore()->getId();
            $currency = $this->expressHelper->getModalCurrencyFromQuote($quote, $scopeId);

            $quote = $this->expressHelper->setUpProductPageQuote(
                $quote,
                $params,
                $scopeId
            );

            if (!empty($shippingAddress)) {
                $this->expressHelper->setShippingAddressToQuoteFromPaymentRequestData(
                    $quote,
                    $shippingAddress,
                    $quote->getQuoteCurrencyCode()
                );
            } else {
                $quote->collectTotals()->save();
            }

            $response = [
                'valid' => 1,
                'options' => $this->expressHelper->getCartOptions($quote, $currency, $scopeId)
            ];
        } catch (Exception $ex) {
            $this->logger->log($ex->getMessage());
            $this->messageManager->addErrorMessage($ex->getMessage());
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
