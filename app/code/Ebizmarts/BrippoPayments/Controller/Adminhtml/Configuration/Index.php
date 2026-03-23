<?php

namespace Ebizmarts\BrippoPayments\Controller\Adminhtml\Configuration;

use Ebizmarts\BrippoPayments\Helper\ConnectedAccounts;
use Ebizmarts\BrippoPayments\Helper\Data as DataHelper;
use Ebizmarts\BrippoPayments\Helper\PlatformService\PlatformService;
use Ebizmarts\BrippoPayments\Helper\RecoverCheckout;
use Exception;
use Ebizmarts\BrippoPayments\Helper\Logger;
use Magento\Backend\Block\Admin\Formkey;
use Magento\Backend\Model\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

class Index extends Action
{
    protected $jsonFactory;
    protected $logger;
    protected $storeManager;
    protected $scopeConfig;
    protected $dataHelper;
    protected $stripeHelper;
    protected $urlBuilder;
    protected $urlBuilderFrontend;
    protected $formKey;
    protected $connectedAccountsHelper;

    /**
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param Logger $logger
     * @param StoreManagerInterface $storeManager
     * @param DataHelper $dataHelper
     * @param UrlInterface $urlBuilder
     * @param \Magento\Framework\UrlInterface $urlBuilderFrontend
     * @param Formkey $formKey
     * @param ConnectedAccounts $connectedAccountsHelper
     */
    public function __construct(
        Context                             $context,
        JsonFactory                         $jsonFactory,
        Logger                              $logger,
        StoreManagerInterface               $storeManager,
        DataHelper                          $dataHelper,
        UrlInterface                        $urlBuilder,
        \Magento\Framework\UrlInterface     $urlBuilderFrontend,
        FormKey                             $formKey,
        ConnectedAccounts                   $connectedAccountsHelper
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->dataHelper = $dataHelper;
        $this->urlBuilder = $urlBuilder;
        $this->urlBuilderFrontend = $urlBuilderFrontend;
        $this->formKey = $formKey;
        $this->connectedAccountsHelper = $connectedAccountsHelper;
    }

    public function execute()
    {
        try {
            $scopeId = $this->storeManager->getStore()->getId();
            $formKey = $this->formKey->getFormKey();
            $urlParams = [
                'form_key' => $formKey
            ];

            $defaultStore = $this->storeManager->getStore();

            $response = [
                'valid' => 1,
                'apiVersion' => '2024-06-20',
                'isServiceReady' => $this->dataHelper->isServiceReady($scopeId),
                'publishableKey' => $this->dataHelper->getPlatformPublishableKey($scopeId),
                'storeUrl' => $this->urlBuilderFrontend->getBaseUrl(),
                'storeName' => $defaultStore->getName(),
                'recoverOrder' => [
                    'defaultMessage' => RecoverCheckout::RECOVER_CHECKOUT_EMAIL_MESSAGE_DEFAULT,
                    'sendRecoverOrderNotification' => $this->urlBuilder->getUrl(
                        'brippo_payments/order/sendRecoverNotification',
                        $urlParams
                    ),
                    'defaultMessageSMS' => RecoverCheckout::RECOVER_CHECKOUT_SMS_MESSAGE_DEFAULT,
                ],
                'onboarding' => [
                    'goToUrl' => PlatformService::SERVICE_URL . PlatformService::ENDPOINT_URI_ONBOARDING,
                    'responseUrl' => $this->urlBuilder->getUrl(
                        DataHelper::ONBOARDING_RESPONSE_URL,
                        [
                            'scope' => "default",
                            'scopeId' => 0
                        ]
                    )
                ],
                'terminals' => [
                    'actionCompleted' => $this->urlBuilder->getUrl(
                        'brippo_payments/terminals/actionCompleted',
                        $urlParams
                    ),
                    'getTerminalStatus' => $this->urlBuilder->getUrl('brippo_payments/terminals/status'),
                    'getTerminals' => $this->urlBuilder->getUrl('brippo_payments/terminals/index'),
                    'processPaymentInTerminal' => $this->urlBuilder->getUrl(
                        'brippo_payments/terminals/process',
                        $urlParams
                    ),
                    'sendReceipt' => $this->urlBuilder->getUrl(
                        'brippo_payments/terminals/sendReceipt',
                        $urlParams
                    )
                ],
                'paymentIntents' => [
                    'getPaymentIntent' => $this->urlBuilder->getUrl('brippo_payments/paymentIntent/index')
                ],
                'connectedAccount' => [
                    'paymentMethodsConfiguration' => $this->connectedAccountsHelper->getPaymentMethods(
                        $this->dataHelper->getAccountId(
                            $scopeId,
                            $this->dataHelper->isLiveMode($scopeId)
                        ),
                        $scopeId
                    )
                ],
                'invoiceOffline' => [
                    'url' => $this->urlBuilder->getUrl(
                        'brippo_payments/order/invoiceOffline',
                        $urlParams
                    )
                ]
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

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ebizmarts_BrippoPayments::configuration');
    }
}
