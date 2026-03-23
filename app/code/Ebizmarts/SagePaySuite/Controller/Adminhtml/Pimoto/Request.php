<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Controller\Adminhtml\Pimoto;

use Ebizmarts\SagePaySuite\Api\Data\PiRequestManagerFactory;
use Ebizmarts\SagePaySuite\Model\Config;
use Ebizmarts\SagePaySuite\Model\PiRequestManagement\MotoManagement;
use Ebizmarts\SagePaySuite\Api\Data\PiRequestManager;
use Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger as SagePaySuiteLogger;
use Magento\Framework\App\ActionInterface;
use Magento\Backend\Model\Session\Quote;
use Magento\Quote\Model\Quote as ModelQuote;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\RequestInterface;
use Magento\GiftMessage\Model\Save;

class Request implements ActionInterface
{
    /** @var Config */
    private $config;

    /** @var ModelQuote */
    private $quote;

    /** @var Quote */
    private $quoteSession;

    /** @var MotoManagement */
    private $requester;

    /** @var PiRequestManager */
    private $piRequestManagerDataFactory;

    /** @var RequestInterface */
    private $request;

    /** @var ResultFactory */
    private $resultFactory;

    /** @var Save */
    private $giftMessageSave;

    /** @var SagePaySuiteLogger $suiteLogger */
    private $suiteLogger;

    /**
     * @param Config $config
     * @param Quote $quoteSession
     * @param MotoManagement $requester
     * @param PiRequestManagerFactory $piReqManagerFactory
     * @param RequestInterface $request
     * @param ResultFactory $resultFactory
     * @param Save $giftMessageSave
     * @param SagePaySuiteLogger $suiteLogger
     */
    public function __construct(
        Config $config,
        Quote $quoteSession,
        MotoManagement $requester,
        PiRequestManagerFactory $piReqManagerFactory,
        RequestInterface $request,
        ResultFactory $resultFactory,
        Save $giftMessageSave,
        SagePaySuiteLogger $suiteLogger
    ) {
        $this->config                       = $config;
        $this->quoteSession                 = $quoteSession;
        $this->quote                        = $this->quoteSession->getQuote();
        $this->requester                    = $requester;
        $this->piRequestManagerDataFactory  = $piReqManagerFactory;
        $this->request                      = $request;
        $this->resultFactory                = $resultFactory;
        $this->giftMessageSave              = $giftMessageSave;
        $this->suiteLogger                  = $suiteLogger;
    }

    public function execute()
    {
        /** @var PiRequestManager $data */
        $data = $this->piRequestManagerDataFactory->create();
        $data->setMode($this->config->getMode());
        $data->setVendorName($this->config->getVendorname());
        $data->setPaymentAction($this->config->getSagepayPaymentAction());
        $data->setMerchantSessionKey($this->getRequest()->getParam('merchant_session_key'));
        $data->setCardIdentifier($this->getRequest()->getParam('card_identifier'));
        $data->setCcExpMonth($this->getRequest()->getParam('card_exp_month'));
        $data->setCcExpYear($this->getRequest()->getParam('card_exp_year'));
        $data->setCcLastFour($this->getRequest()->getParam('card_last4'));
        $data->setCcType($this->getRequest()->getParam('card_type'));
        $data->setJavascriptEnabled($this->getRequest()->getParam('javascript_enabled'));
        $data->setAcceptHeaders($this->getRequest()->getParam('accept_headers'));
        $data->setLanguage($this->getRequest()->getParam('language'));
        $data->setUserAgent($this->getRequest()->getParam('user_agent'));
        $data->setJavaEnabled($this->getRequest()->getParam('java_enabled'));
        $data->setColorDepth($this->getRequest()->getParam('color_depth'));
        $data->setScreenWidth($this->getRequest()->getParam('screen_width'));
        $data->setScreenHeight($this->getRequest()->getParam('screen_height'));
        $data->setTimezone($this->getRequest()->getParam('timezone'));
        $data->setSaveToken($this->getRequest()->getParam('save_token'));
        $data->setReusableToken($this->getRequest()->getParam('reusable_token'));

        $this->requester->setRequestData($data);
        $this->requester->setQuote($this->quote);

        $giftmessages = $this->getRequest()->getPost('giftmessage');
        if ($giftmessages) {
            $this->giftMessageSave->setGiftmessages($giftmessages)->saveAllInQuote();
        }

        $this->suiteLogger->orderStartLog('PI MOTO', $this->quote->getReservedOrderId(), $this->quote->getId());

        $response = $this->requester->placeOrder();

        $this->suiteLogger->orderEndLog(
            $response->getOrderId(),
            $response->getQuoteId(),
            $response->getTransactionId()
        );

        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($response->__toArray());
        return $resultJson;
    }

    /**
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }
}
