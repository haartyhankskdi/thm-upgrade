<?php

namespace Ebizmarts\SagePaySuite\Model;

use Ebizmarts\SagePaySuite\Api\Data\PiRequest;
use Ebizmarts\SagePaySuite\Api\Data\PiResultInterface;
use Ebizmarts\SagePaySuite\Model\SessionInterface as SagePaySession;
use Magento\Checkout\Model\Session;

class MsPiRequestManagement implements \Ebizmarts\SagePaySuite\Api\MsPiManagementInterface
{
    /**
     * @var PiResultInterface
     */
    private $piResultInterface;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var MsScaData
     */
    private $msScaModel;

    /**
     * MsPiRequestManagement constructor.
     * @param PiResultInterface $piResultInterface
     * @param Session $checkoutSession
     * @param \Ebizmarts\SagePaySuite\Model\MsScaData $msScaModel
     */
    public function __construct(
        PiResultInterface $piResultInterface,
        \Magento\Checkout\Model\Session $checkoutSession,
        MsScaData $msScaModel
    ) {
        $this->piResultInterface = $piResultInterface;
        $this->checkoutSession = $checkoutSession;
        $this->msScaModel = $msScaModel;
    }

    /**
     * @inheritDoc
     */
    public function handleTransactionData($cartId, PiRequest $requestData)
    {
        $result = $this->saveCardToken($cartId, $requestData);
        $this->saveScaParams($cartId, $requestData);

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function saveCardToken($cartId, PiRequest $requestData)
    {
        $this->checkoutSession->setData(SagePaySession::CARD_IDENTIFIER, $requestData->getCardIdentifier());
        $this->checkoutSession->setData(SagePaySession::MERCHANT_SESSION_KEY, $requestData->getMerchantSessionKey());

        if ($this->checkoutSession->getData('sagepaysuite_merchant_session_key') !== null
            && $this->checkoutSession->getData('sagepaysuite_card_identifier') !== null) {
            $this->piResultInterface->setStatus('Ok');
            $this->piResultInterface->setSuccess(1);
        }

        return $this->piResultInterface;
    }

    /**
     * @inheritDoc
     */
    public function saveScaParams($cartId, PiRequest $requestData)
    {
        if (!empty($requestData)) {
            $jsEnabled = $requestData->getJavascriptEnabled();
            $javaEnabled = $requestData->getJavaEnabled();
            $colorDepth = $requestData->getColorDepth();
            $screenHeight = $requestData->getScreenHeight();
            $screenWidth = $requestData->getScreenWidth();
            $timeZone = $requestData->getTimezone();
            $acceptHeaders = $requestData->getAcceptHeaders();
            $language = $requestData->getLanguage();
            $userAgent = $requestData->getUserAgent();

            $this->msScaModel->setQuoteId($cartId);
            $this->msScaModel->setJsEnabled($jsEnabled);
            $this->msScaModel->setJavaEnabled($javaEnabled);
            $this->msScaModel->setColorDepth($colorDepth);
            $this->msScaModel->setScreenHeight($screenHeight);
            $this->msScaModel->setScreenWidth($screenWidth);
            $this->msScaModel->setTimeZone($timeZone);
            $this->msScaModel->setAcceptHeader($acceptHeaders);
            $this->msScaModel->setLanguage($language);
            $this->msScaModel->setUserAgent($userAgent);
            $this->msScaModel->save();
        }
    }
}
