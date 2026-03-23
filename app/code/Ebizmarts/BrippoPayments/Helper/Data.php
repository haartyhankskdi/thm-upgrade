<?php

namespace Ebizmarts\BrippoPayments\Helper;

use Ebizmarts\BrippoPayments\Model\Config\Source\Mode;
use Exception;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Backend\Model\UrlInterface as UrlBackend;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Math\Random;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Model\Config as TaxConfig;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Module\Dir as ModuleDir;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Cache\Manager as CacheManager;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Invoice;

class Data extends AbstractHelper
{
    /* @codingStandardsIgnoreStart */
    const PUBLISHABLE_KEY_TEST = 'pk_test_51MbnoqJFieew03j4sdSk76oQ2k51yOFJEEJI1knu0v7i5u3ZB0nkHN3hLYgEm50XTPpP9Zg8lfryu00QIPeGWWr200Zud5BEw6';
    const PUBLISHABLE_KEY_LIVE = 'pk_live_51MbnoqJFieew03j4qkmWuus8WgNHjd4y1TJg17nKQJohxNdViP9ag5FhhTAkYwRFp60dwpAhpMCwh6KqDsVTQCDv00mStBKoz7';
    /* @codingStandardsIgnoreEnd */

    /** GENERAL */
    const ACCOUNT_ID_CONFIG_PATH                = 'brippo_payments/global/exp_account_id';
    const ACCOUNT_COUNTRY_CONFIG_PATH_PREFIX    = 'brippo_payments/global/exp_account_country_';
    const ACCESS_TOKEN_CONFIG_PATH              = 'brippo_payments/global/exp_access_token';
    const PUBLISHABLE_KEY_CONFIG_PATH           = 'brippo_payments/global/exp_publishable_key';
    const MODE_CONFIG_PATH                      = 'brippo_payments/global/mode';
    const OAUTH_HASHKEY_CONFIG_PATH             = 'brippo_payments/global/hash_key';
    const XML_PATH_MONITOR                      = 'brippo_payments/global/payments_monitor';
    const XML_PATH_CUSTOMER_INFO                = 'brippo_payments/global/hide_customer_info';
    const XML_PATH_CURRENCY_MODE                = 'brippo_payments/global/currency_mode';
    const XML_PATH_DEBUG_MODE                   = 'brippo_payments/global/debug_mode';
    const XML_PATH_STATEMENT_DESCRIPTOR_SUFFIX  = 'brippo_payments/global/statement_descriptor_suffix';
    const XML_PATH_APPLICABLE_COUNTRY_ACTIVE    = 'brippo_payments/global/specific_countries_active';
    const XML_PATH_APPLICABLE_COUNTRY           = 'brippo_payments/global/specific_country';
    const NOTIFY_UNCAPTURED_TRANSACTIONS_ACTIVE_CONFIG_PATH = 'brippo_payments/notifications/uncaptured_transactions';
    const ONBOARDING_RESET_URL                  = 'brippo_payments/onboarding/reset';
    const ONBOARDING_RESPONSE_URL               = 'brippo_payments/onboarding/response';
    const CONFIG_PATH_BRIPPO_API_KEY            = 'brippo_payments/global/api_key';

    protected const MINIMUM_AMOUNTS_ALLOWED = [
        'USD' => 0.5,
        'AED' => 2.00,
        'AUD' => 0.5,
        'BGN' => 1.00,
        'BRL' => 0.5,
        'CAD' => 0.5,
        'CHF' => 0.5,
        'CZK' => 15.00,
        'DKK' => 2.50,
        'EUR' => 0.5,
        'GBP' => 0.3,
        'HKD' => 4.00,
        'HUF' => 175.00,
        'INR' => 0.5,
        'JPY' => 50,
        'MXN' => 10,
        'MYR' => 2,
        'NOK' => 3.00,
        'NZD' => 0.5,
        'PLN' => 2.00,
        'RON' => 2.00,
        'SEK' => 3.00,
        'SGD' => 0.5,
        'THB' => 10
    ];

    /** WEBHOOKS */
    const WEBHOOKS_ENDPOINT = 'brippo_payments/webhooks/triage';

    public $logger;
    protected $configWriter;
    protected $urlBackend;
    protected $taxConfig;
    protected $storeManager;
    protected $encryptor;
    protected $request;
    protected $moduleDir;
    protected $driverFile;
    protected $json;
    public $cache;
    protected $mathRandom;
    public $cacheManager;
    protected $productMetadata;

    /** @var RemoteAddress */
    protected $remoteAddress;

    /**
     * @var AdapterInterface
     */
    protected $connection;

    /**
     * @param Context $context
     * @param Logger $logger
     * @param ScopeConfigInterface $scopeConfig
     * @param WriterInterface $configWriter
     * @param UrlBackend $urlBackend
     * @param TaxConfig $taxConfig
     * @param StoreManagerInterface $storeManager
     * @param EncryptorInterface $encryptor
     * @param RequestInterface $request
     * @param ModuleDir $moduleDir
     * @param File $driverFile
     * @param Json $json
     * @param CacheInterface $cache
     * @param CacheManager $cacheManager
     * @param Random $mathRandom
     * @param ProductMetadataInterface $productMetadata
     * @param RemoteAddress $remoteAddress
     * @param ResourceConnection $resource
     */
    public function __construct(
        Context                     $context,
        Logger                      $logger,
        ScopeConfigInterface    $scopeConfig,
        WriterInterface         $configWriter,
        UrlBackend              $urlBackend,
        TaxConfig               $taxConfig,
        StoreManagerInterface   $storeManager,
        EncryptorInterface      $encryptor,
        RequestInterface        $request,
        ModuleDir               $moduleDir,
        File                    $driverFile,
        Json                    $json,
        CacheInterface          $cache,
        CacheManager            $cacheManager,
        Random                  $mathRandom,
        ProductMetadataInterface $productMetadata,
        RemoteAddress           $remoteAddress,
        ResourceConnection $resource
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->configWriter = $configWriter;
        $this->urlBackend = $urlBackend;
        $this->taxConfig = $taxConfig;
        $this->storeManager = $storeManager;
        $this->encryptor = $encryptor;
        $this->request = $request;
        $this->moduleDir = $moduleDir;
        $this->driverFile = $driverFile;
        $this->json = $json;
        $this->cache = $cache;
        $this->cacheManager = $cacheManager;
        $this->mathRandom = $mathRandom;
        $this->productMetadata = $productMetadata;
        $this->remoteAddress = $remoteAddress;
        $this->connection = $resource->getConnection();
    }

    /**
     * @param $scopeId
     * @param bool $liveMode
     * @param string $scopeType
     * @return string|null
     */
    public function getAccountId(
        $scopeId = null,
        bool $liveMode = true,
        string $scopeType = ScopeInterface::SCOPE_STORE
    ): ?string {
        return $this->scopeConfig->getValue(
            self::ACCOUNT_ID_CONFIG_PATH . ($liveMode ? '' : '_test'),
            $scopeType,
            $scopeId
        );
    }

    /**
     * @param $scopeId
     * @param string $scopeType
     * @param bool $liveMode
     * @return bool
     */
    public function hasAccountId($scopeId, string $scopeType, bool $liveMode = true): bool
    {
        return !empty($this->scopeConfig->getValue(
            self::ACCOUNT_ID_CONFIG_PATH . ($liveMode ? '' : '_test'),
            $scopeType,
            $scopeId
        ));
    }

    /**
     * @param string $accountId
     * @param string $scopeType
     * @param $scopeId
     * @param bool $liveMode
     * @return void
     */
    public function saveAccountId(string $accountId, string $scopeType, $scopeId, bool $liveMode = true): void
    {
        $this->configWriter->save(
            self::ACCOUNT_ID_CONFIG_PATH . ($liveMode ? '' : '_test'),
            $accountId,
            $scopeType,
            $scopeId
        );
    }

    /**
     * @param bool $liveMode
     * @param string $scopeType
     * @param $scopeId
     * @return void
     */
    public function saveLiveMode(bool $liveMode, string $scopeType, $scopeId): void
    {
        $this->configWriter->save(
            self::MODE_CONFIG_PATH,
            $liveMode ? Mode::MODE_LIVE : Mode::MODE_TEST,
            $scopeType,
            $scopeId
        );
    }

    /**
     * @param string $accountId
     * @param string $country
     * @param $scopeId
     * @param string $scopeType
     * @return void
     */
    public function saveAccountCountry(
        string $accountId,
        string $country,
        $scopeId,
        string $scopeType = ScopeInterface::SCOPE_STORE
    ): void {
        $this->configWriter->save(
            self::ACCOUNT_COUNTRY_CONFIG_PATH_PREFIX . $accountId,
            $country,
            $scopeType,
            $scopeId
        );
    }

    /**
     * @param $scopeId
     * @param string $scopeType
     * @return bool
     */
    public function isLiveMode($scopeId = null, string $scopeType = ScopeInterface::SCOPE_STORE): bool
    {
        return $this->scopeConfig->getValue(
            self::MODE_CONFIG_PATH,
            $scopeType,
            $scopeId
        ) == Mode::MODE_LIVE;
    }

    /**
     * @param string $key
     * @param $storeId
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getStoreConfig(string $key, $storeId = null)
    {
        if (!$storeId) {
            $storeId = $this->getStoreId();
        }

        return $this->scopeConfig->getValue(
            $key,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @return int
     * @throws NoSuchEntityException
     */
    protected function getStoreId(): int
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * @return StoreInterface
     * @throws NoSuchEntityException
     */
    public function getStore(): StoreInterface
    {
        return $this->storeManager->getStore();
    }

    /**
     * @param $scopeId
     * @return bool
     */
    public function isServiceReady($scopeId = null): bool
    {
        $liveMode = $this->isLiveMode($scopeId);
        return $this->hasAccountId($scopeId, ScopeInterface::SCOPE_STORE, $liveMode);
    }

    /**
     * @param string $hash
     * @return void
     */
    private function saveOauthHashKey(string $hash): void
    {
        $this->configWriter->save(
            self::OAUTH_HASHKEY_CONFIG_PATH,
            $hash
        );
    }

    /**
     * @return string|null
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getOauthHashKey(): ?string
    {
        $hashKey = $this->getStoreConfig(self::OAUTH_HASHKEY_CONFIG_PATH, 0);
        if (empty($hashKey)) {
            $hashKey = $this->mathRandom->getUniqueHash();
            $this->saveOauthHashKey($hashKey);
            $this->cacheManager->flush($this->cacheManager->getAvailableTypes());
            $this->cacheManager->clean($this->cacheManager->getAvailableTypes());
        }
        return $hashKey;
    }

    /**
     * @return string
     */
    public function getStoreUrl(): string
    {
        return $this->_urlBuilder->getBaseUrl();
    }

    /**
     * @return string
     */
    public function getStoreDomain(): string
    {
        return explode('/', $this->getStoreUrl())[2];
    }

    /**
     * @param $url
     * @return array|mixed|string|string[]
     */
    public function cleanDomain($url)
    {
        if (empty($url)) {
            return $url;
        }
        if (substr($url, -1) === '/') {
            $url = rtrim($url, '/');
        }
        $url = str_replace(['http://', 'https://'], '', $url);
        return $url;
    }

    /**
     * @return string
     */
    public function getClientIpAddress(): string
    {
        try {
            // 1) Cloudflare’s original client IP
            $cfIp = $this->request->getServerValue('HTTP_CF_CONNECTING_IP');
            if (!empty($cfIp)) {
                return trim((string)$cfIp);
            }

            // 2) Standard “real IP” header (if you ever use it)
            $realIp = $this->request->getServerValue('HTTP_X_REAL_IP');
            if (!empty($realIp)) {
                return trim((string)$realIp);
            }

            // 3) X-Forwarded-For (may contain multiple comma-separated IPs)
            $forwarded = $this->request->getServerValue('HTTP_X_FORWARDED_FOR');
            if (!empty($forwarded)) {
                $parts = explode(',', (string)$forwarded);
                $firstIp = trim($parts[0]);
                // Validate IP format before returning
                if (filter_var($firstIp, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $firstIp;
                }
            }

            // 4) Fallback to Magento’s RemoteAddress
            $remoteAddress = $this->remoteAddress->getRemoteAddress();
            if (!empty($remoteAddress)) {
                return trim((string)$remoteAddress);
            }
        } catch (Exception $ex) {
            $this->logger->log($ex->getMessage());
        }

        return '';
    }

    /**
     * @return string
     */
    public function getScopeTypeFromUrl(): string
    {
        if ($this->request->getParam(ScopeInterface::SCOPE_STORE)) {
            return ScopeInterface::SCOPE_STORES;
        } elseif ($this->request->getParam(ScopeInterface::SCOPE_WEBSITE)) {
            return ScopeInterface::SCOPE_WEBSITES;
        } else {
            return ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
        }
    }

    /**
     * @return int
     */
    public function getScopeIdFromUrl(): int
    {
        $params  = $this->request->getParams();
        $scopeId = 0;

        if (isset($params[ScopeInterface::SCOPE_STORE])) {
            // If store param given, just return it
            $scopeId = (int) $params[ScopeInterface::SCOPE_STORE];
        } elseif (isset($params[ScopeInterface::SCOPE_WEBSITE])) {
            // Resolve website to its default store
            $websiteId = (int) $params[ScopeInterface::SCOPE_WEBSITE];
            try {
                $website = $this->storeManager->getWebsite($websiteId);
                $defaultStore = $website->getDefaultStore();
                if ($defaultStore) {
                    $scopeId = $defaultStore->getId();
                }
            } catch (NoSuchEntityException $e) {
                $this->logger->log('Invalid website id');
                $this->logger->log($e->getMessage());
            } catch (LocalizedException $e) {
                $this->logger->log('Invalid website id');
                $this->logger->log($e->getMessage());
            }
        }

        return $scopeId;
    }

    /**
     * @param $scopeId
     * @return string
     */
    public function getPlatformPublishableKey($scopeId = null): string
    {
        if ($this->isLiveMode($scopeId)) {
            return self::PUBLISHABLE_KEY_LIVE;
        } else {
            return self::PUBLISHABLE_KEY_TEST;
        }
    }

    /**
     * @return string
     */
    public function getExtensionsVersionString(): string
    {
        try {
            $cacheKey = 'brippo_payments_versions_string';
            $valueSavedInCache = $this->cache->load($cacheKey);
            if (empty($valueSavedInCache)) {
                /*
                 * CORE MODULE
                 */
                try {
                    $moduleCodeFolder = $this->moduleDir->getDir('Ebizmarts_BrippoPayments');
                    $composerJsonFilePath = $moduleCodeFolder . '/composer.json';
                    $contents = $this->json->unserialize($this->driverFile->fileGetContents($composerJsonFilePath));
                    $coreModuleString = 'Payments ' . $contents['version'];
                } catch (Exception $ex) {
                    $coreModuleString = '';
                }

                /*
                 * POS MODULE
                 */
                try {
                    $moduleCodeFolder = $this->moduleDir->getDir('Ebizmarts_PosBrippo');
                    $composerJsonFilePath = $moduleCodeFolder . '/composer.json';
                    $contents = $this->json->unserialize($this->driverFile->fileGetContents($composerJsonFilePath));
                    $posModuleString = ', POS ' . $contents['version'];
                } catch (Exception $ex) {
                    $posModuleString = '';
                }

                $generated = $coreModuleString . $posModuleString;
                $this->cache->save($generated, $cacheKey);
                return $generated;
            } else {
                return $valueSavedInCache;
            }
        } catch (Exception $ex) {
            $this->logger->log($ex->getMessage());
            return '';
        }
    }

    /**
     * @param CartInterface $quote
     * @return bool
     */
    public function isAboveMinimumAmountAllowed(CartInterface $quote): bool
    {
        $currencyCode = $quote->getCurrency()->getQuoteCurrencyCode();
        $grandTotal = $quote->getGrandTotal();

        if (isset(self::MINIMUM_AMOUNTS_ALLOWED[$currencyCode])
            && $grandTotal < self::MINIMUM_AMOUNTS_ALLOWED[$currencyCode]) {
            return false;
        }

        return true;
    }

    public function isOrderInvoicePending(OrderInterface $order): bool
    {
        $invoices = $order->getInvoiceCollection();
        $invoices->setOrder('created_at', 'DESC');
        $lastInvoice = $invoices->getFirstItem();

        return $lastInvoice->getState() == Invoice::STATE_OPEN;
    }

    /**
     * @param $scopeId
     * @return string
     */
    public function getWebhookUrl($scopeId): string
    {
        return $this->_getUrl(
            self::WEBHOOKS_ENDPOINT,
            ['scopeId' => $scopeId]
        );
    }

    /**
     * @return string
     */
    public function getMagentoEditionString()
    {
        return $this->productMetadata->getEdition();
    }

    /**
     * @return string
     */
    public function getMagentoVersionString()
    {
        return $this->productMetadata->getVersion();
    }

    public function hideCustomerInfo($input)
    {
        if (!$this->getStoreConfig(self::XML_PATH_CUSTOMER_INFO)) {
            return $input;
        }
        $words = explode(" ", (string)$input);
        $result = [];

        foreach ($words as $word) {
            if (strlen($word) > 2) {
                $word = $word[0] . str_repeat('*', strlen($word) - 2) . $word[strlen($word) - 1];
            }
            $result[] = $word;
        }
        return implode(" ", $result);
    }

    /**
     * @return bool
     */
    public function isMonitorRunning()
    {
        $monitor_job = "brippo_payments_monitor";
        $table = $this->connection->getTableName('cron_schedule');

        $query = $this->connection->select()
            ->from($table)
            ->where('job_code = ?', $monitor_job)
            ->where('status = ?', 'success')
            ->where('executed_at > (NOW() - INTERVAL 30 MINUTE)');

        $result = $this->connection->fetchRow($query);

        return !empty($result);
    }

    /**
     * @param $order
     * @return bool
     */
    public function wasOrderPaidWithBrippo($order): bool
    {
        return !empty($order)
            && !empty($order->getPayment())
            && !empty($order->getPayment()->getMethod())
            && strpos($order->getPayment()->getMethod(), 'brippo') !== false;
    }

    /**
     * @param $request
     * @return array
     * @throws LocalizedException
     */
    public function unserializeRequestBody($request): array
    {
        try {
            if (!empty($request)
                && method_exists($request, 'getContent')
                && !empty($request->getContent())) {
                $jsonVars = $this->json->unserialize($request->getContent());
                if (is_array($jsonVars) && !empty($jsonVars)) {
                    return $jsonVars;
                } else {
                    throw new LocalizedException(__('Unserialization failed'));
                }
            }
        } catch (Exception $e) {
            $this->logger->log("Failed to unserialize request body: ". $e->getMessage());
            $this->logger->log($e->getTraceAsString());
            throw $e;
        }

        return [];
    }

    /**
     * @param OrderInterface $order
     * @return string
     * @throws NoSuchEntityException
     */
    public function getStoreNameFromOrder(OrderInterface $order): string
    {
        $storeId = $order->getStoreId();
        $store = $this->storeManager->getStore($storeId);
        return $store->getName();
    }
}
