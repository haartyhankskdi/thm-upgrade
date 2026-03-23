<?php
namespace Ebizmarts\BrippoPayments\Helper;

use Ebizmarts\BrippoPayments\Helper\Data as DataHelper;
use Exception;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Ebizmarts\BrippoPayments\Helper\BrippoApi\ConnectedAccounts as ConnectedAccountsApi;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;

class ConnectedAccounts extends AbstractHelper
{
    protected $dataHelper;
    protected $brippoConnectedAccountsApi;
    protected $serializer;

    const CACHE_KEY_ACCOUNT_COUNTRY = 'brippo_payments_account_country_';
    const CACHE_KEY_ACCOUNT_PAYMENT_METHODS = 'brippo_payments_account_payment_methods_';


    /**
     * @param Context $context
     * @param Data $dataHelper
     * @param ConnectedAccountsApi $brippoConnectedAccountsApi
     * @param SerializerInterface $serializer
     */
    public function __construct(
        Context $context,
        DataHelper $dataHelper,
        ConnectedAccountsApi $brippoConnectedAccountsApi,
        SerializerInterface $serializer
    ) {
        parent::__construct($context);
        $this->dataHelper = $dataHelper;
        $this->brippoConnectedAccountsApi = $brippoConnectedAccountsApi;
        $this->serializer = $serializer;
    }

    /**
     * @param string $accountId
     * @param int $scopeId
     * @return string
     */
    public function getCountry(string $accountId, int $scopeId): string
    {
        try {
            $cacheKey = self::CACHE_KEY_ACCOUNT_COUNTRY . $accountId;
            $valueSavedInCache = $this->dataHelper->cache->load($cacheKey);
            if (empty($valueSavedInCache)) {
                $this->retrieveConnectedAccountConfiguration($accountId, $scopeId);
                $valueSavedInCache = $this->dataHelper->cache->load($cacheKey);
                if (empty($valueSavedInCache)) {
                    throw new LocalizedException(__('Unable to retrieve account country. defaulting...'));
                } else {
                    return $valueSavedInCache;
                }
            } else {
                return $valueSavedInCache;
            }
        } catch (Exception $ex) {
            $this->dataHelper->logger->log($ex->getMessage());
            return 'GB';
        }
    }

    /**
     * @param $accountId
     * @param int $scopeId
     * @param bool $forceRefresh
     * @return array|bool|float|int|string|null
     */
    public function getPaymentMethods($accountId, int $scopeId, bool $forceRefresh = false)
    {
        try {
            if (empty($accountId)) {
                return null;
            }

            $cacheKey = self::CACHE_KEY_ACCOUNT_PAYMENT_METHODS . $accountId;
            $valueSavedInCache = $this->dataHelper->cache->load($cacheKey);
            if (empty($valueSavedInCache) || $forceRefresh) {
                $this->retrieveConnectedAccountConfiguration($accountId, $scopeId);
                $valueSavedInCache = $this->dataHelper->cache->load($cacheKey);
                if (empty($valueSavedInCache)) {
                    throw new LocalizedException(
                        __('Unable to retrieve account payment methods configuration. defaulting...')
                    );
                } else {
                    return $this->serializer->unserialize($valueSavedInCache);
                }
            } else {
                return $this->serializer->unserialize($valueSavedInCache);
            }
        } catch (Exception $ex) {
            $this->dataHelper->logger->log($ex->getMessage());
            return null;
        }
    }

    /**
     * @param string $accountId
     * @param int $scopeId
     * @return void
     * @throws LocalizedException
     */
    function retrieveConnectedAccountConfiguration(string $accountId, int $scopeId)
    {
        $liveMode = $this->dataHelper->isLiveMode($scopeId);
        $connectedAccount = $this->brippoConnectedAccountsApi->get($liveMode, $accountId);
        $this->dataHelper->logger->log('Retrieved connected account settings online. Saving...');

        /*
         * COUNTRY
         */
        $accountCountryOnline = $connectedAccount[Stripe::PARAM_COUNTRY];
        $this->dataHelper->saveAccountCountry($accountId, $accountCountryOnline, $scopeId);
        $this->dataHelper->cache->save(
            $accountCountryOnline,
            self::CACHE_KEY_ACCOUNT_COUNTRY . $accountId
        );

        /*
         * PAYMENT METHODS CONFIGURATION
         */
        $paymentMethodsOnline = $connectedAccount[Stripe::PARAM_PAYMENT_METHODS];
        $this->dataHelper->cache->save(
            $this->serializer->serialize($paymentMethodsOnline),
            self::CACHE_KEY_ACCOUNT_PAYMENT_METHODS . $accountId
        );
    }
}
