<?php

namespace Ebizmarts\BrippoPayments\Controller\ExpressCheckout;

use Ebizmarts\BrippoPayments\Helper\ConnectedAccounts;
use Ebizmarts\BrippoPayments\Helper\Express as ExpressHelper;
use Ebizmarts\BrippoPayments\Model\Config\Source\ExpressLocation;
use Ebizmarts\BrippoPayments\Model\Config\Source\Wallets;
use Ebizmarts\BrippoPayments\Model\Express;
use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Ebizmarts\BrippoPayments\Helper\Logger;
use Ebizmarts\BrippoPayments\Helper\Data as DataHelper;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Model\QuoteFactory;
use Magento\Store\Model\StoreManagerInterface;

class PaymentRequest extends Action
{
    protected $jsonFactory;
    protected $logger;
    protected $checkoutSession;
    protected $dataHelper;
    protected $expressHelper;
    protected $quoteFactory;
    protected $storeManager;
    protected $connectedAccountsHelper;

    /**
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param Logger $logger
     * @param DataHelper $dataHelper
     * @param CheckoutSession $checkoutSession
     * @param ExpressHelper $expressHelper
     * @param QuoteFactory $quoteFactory
     * @param StoreManagerInterface $storeManager
     * @param ConnectedAccounts $connectedAccountsHelper
     */
    public function __construct(
        Context                             $context,
        JsonFactory                         $jsonFactory,
        Logger                              $logger,
        DataHelper                          $dataHelper,
        CheckoutSession                     $checkoutSession,
        ExpressHelper                       $expressHelper,
        QuoteFactory                        $quoteFactory,
        StoreManagerInterface               $storeManager,
        ConnectedAccounts                   $connectedAccountsHelper
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->logger = $logger;
        $this->dataHelper = $dataHelper;
        $this->checkoutSession = $checkoutSession;
        $this->expressHelper = $expressHelper;
        $this->quoteFactory = $quoteFactory;
        $this->storeManager = $storeManager;
        $this->connectedAccountsHelper = $connectedAccountsHelper;
    }

    public function execute()
    {
        $this->expressHelper->setParamsFromRequestBody($this->getRequest());
        $scopeId = $this->storeManager->getStore()->getId();
        $currentProductId = $this->getRequest()->getParam('currentProductId');
        $source = $this->getRequest()->getParam('source');

        try {
            $quote = $this->checkoutSession->getQuote();

            if ($source == ExpressLocation::PRODUCT_PAGE
                && (empty($quote->getId()) || $quote->getGrandTotal() == 0)) {
                $quote = $this->expressHelper->createQuoteFromScratch($currentProductId);
            }

            $currency = $this->expressHelper->getModalCurrencyFromQuote($quote, $scopeId);
            $shippingOptionsToSend = $this->expressHelper->getShippingOptions($quote, $currency);
            $cartTotalOptions = $this->expressHelper->getCartOptions($quote, $currency, $scopeId);
            $defaultWallets = [Wallets::APPLE_PAY, Wallets::GOOGLE_PAY, Wallets::LINK];
            $disabledWallets = [Wallets::BROWSER_CARD];
            $enabledWallets = explode(',', (string)$this->dataHelper->getStoreConfig(
                Express::XML_PATH_STORE_CONFIG_WALLETS,
                $scopeId
            ));
            foreach ($defaultWallets as $wallet) {
                if (!in_array($wallet, $enabledWallets)) {
                    $disabledWallets []= $wallet;
                }
            }

            $accountId = $this->dataHelper->getAccountId(
                $scopeId,
                $this->dataHelper->isLiveMode($scopeId)
            );

            $response = [
                'valid' => 1,
                'options' => array_merge(
                    $cartTotalOptions,
                    [
                        'requestPayerName' => true,
                        'requestPayerEmail' => true,
                        'requestPayerPhone' => true,
                        'disableWallets' => $disabledWallets,
                        'country' => $this->connectedAccountsHelper->getCountry($accountId, $scopeId)
                    ]
                ),
                'coupon' => $quote->getCouponCode()
            ];

            if ($this->expressHelper->shouldRequestShipping(
                $quote,
                $source == null ? "" : $source,
                $scopeId
            )) {
                $response['options']['requestShipping'] = true;
                $response['options']['shippingOptions'] = $shippingOptionsToSend;
            } else {
                $response['options']['requestShipping'] = false;
            }

            $this->checkoutSession->setBrippoRequestedAmount($quote->getGrandTotal());
            $this->expressHelper->generateOrderUniqId($this->checkoutSession);
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
