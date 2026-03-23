<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Model;

class PiMsk implements \Ebizmarts\SagePaySuite\Api\PiMerchantInterface
{
    /** @var \Ebizmarts\SagePaySuite\Model\Api\PIRest */
    private $piRestApi;

    /** @var \Ebizmarts\SagePaySuite\Api\Data\ResultInterface */
    private $result;

    /** @var \Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger */
    private $log;

    /** @var \Magento\Store\Model\StoreManagerInterface */
    private $storeManager;

    /** @var \Magento\Quote\Model\QuoteFactory */
    private $quoteFactory;

    public function __construct(
        \Ebizmarts\SagePaySuite\Model\Api\PIRest $piRestApi,
        \Ebizmarts\SagePaySuite\Api\Data\ResultInterface $result,
        \Ebizmarts\SagePaySuiteLogger\Model\Logger\Logger $log,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Quote\Model\QuoteFactory $quoteFactory
    ) {
        $this->piRestApi = $piRestApi;
        $this->result    = $result;
        $this->log       = $log;
        $this->storeManager = $storeManager;
        $this->quoteFactory = $quoteFactory;
    }

    private function setPaymentMethod($isMoto)
    {
        $this->piRestApi->setPaymentMethod($isMoto);
    }

    /**
     * @inheritdoc
     */
    public function getSessionKey(\Magento\Quote\Api\Data\CartInterface $quote = null, $storeId = null, $isMoto = false)
    {
        try {
            $this->setPaymentMethod($isMoto);
            if ($storeId === null) {
                if ($quote === null) {
                    $quote = $this->getDummyQuote();
                }
                $storeId = $quote->getStoreId();
            }

            $merchantSessionKey = $this->piRestApi->generateMerchantKey($storeId);

            $this->result->setSuccess(true);
            $this->result->setResponse($merchantSessionKey->getMerchantSessionKey());
        } catch (\Ebizmarts\SagePaySuite\Model\Api\ApiException $apiException) {
            $this->result->setSuccess(false);
            $this->result->setErrorMessage(__($apiException->getUserMessage()));
            $this->log->logException($apiException, [__METHOD__, __LINE__]);
        } catch (\Exception $e) {
            $this->result->setSuccess(false);
            $this->result->setErrorMessage(__('Something went wrong while generating the merchant session key.'));
            $this->log->logException($e, [__METHOD__, __LINE__]);
        }

        return $this->result;
    }

    private function getDummyQuote()
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteFactory->create();
        return $quote;
    }
}
