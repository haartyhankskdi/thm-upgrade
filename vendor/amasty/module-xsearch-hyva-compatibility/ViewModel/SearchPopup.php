<?php
declare(strict_types=1);
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Hyva Compatibility M2 by Amasty
 */

namespace Amasty\XsearchHyvaCompatibility\ViewModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\View\Element\Template;
use Magento\Search\Model\Query as SearchQuery;
use Magento\Search\Model\QueryFactory;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Module\Manager as ModuleManager;

/**
 * GraphQl queries for search popup
 */
class SearchPopup implements ArgumentInterface
{
    /**
     * @var Http
     */
    private Http $request;

    /**
     * @var UrlInterface
     */
    private UrlInterface $urlBuilder;

    /**
     * @var StringUtils
     */
    private StringUtils $string;

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /** @var ModuleManager  */
    private ModuleManager $moduleManager;

    /** @var Template */
    private Template $template;

    /**
     * @param UrlInterface $urlBuilder
     * @param Http $request
     * @param StringUtils $string
     * @param ScopeConfigInterface $scopeConfig
     * @param ModuleManager $moduleManager
     * @param Template $template
     */
    public function __construct(
        UrlInterface         $urlBuilder,
        Http                 $request,
        StringUtils          $string,
        ScopeConfigInterface $scopeConfig,
        ModuleManager        $moduleManager,
        Template             $template
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->request = $request;
        $this->string = $string;
        $this->scopeConfig = $scopeConfig;
        $this->moduleManager = $moduleManager;
        $this->template = $template;
    }

    /**
     * Retrieve result page url
     *
     * @param   string $query
     * @return  string
     */
    public function getResultsUrl($query = null): string
    {
        return $this->urlBuilder->getUrl(
            'catalogsearch/result',
            [
                '_query' => [QueryFactory::QUERY_VAR_NAME => $query],
                '_secure' => $this->request->isSecure()
            ]
        );
    }

    /**
     * Retrieve result page url
     *
     * @return  string
     */
    public function getResultsPageUrl(): string
    {
        $isCustomEnabled = $this->scopeConfig->isSetFlag(
            'amasty_xsearch/general/enable_seo_url',
            ScopeInterface::SCOPE_STORE
        );

        if (!$isCustomEnabled) {
            return $this->urlBuilder->getUrl(
                'catalogsearch/result',
                ['_secure' => $this->request->isSecure()]
            ) . '?q=';
        }

        $customUrl = $this->scopeConfig->getValue(
            'amasty_xsearch/general/seo_key',
            ScopeInterface::SCOPE_STORE
        );

        return $this->urlBuilder->getUrl($customUrl, [
            '_secure' => $this->request->isSecure()
        ]);
    }

    /**
     * Retrieve param name for search query
     *
     * @return string
     */
    public function getQueryParamName(): string
    {
        return QueryFactory::QUERY_VAR_NAME;
    }

    /**
     * Retrieve search query text
     *
     * @return string
     */
    public function getQueryText(): string
    {
        $queryText = $this->request->getParam($this->getQueryParamName());
        return($queryText === null || is_array($queryText))
            ? ''
            : $this->string->cleanString(trim($queryText));
    }

    /**
     * Retrieve maximum query length
     *
     * @param mixed $store
     * @return string
     */
    public function getMaxQueryLength($store = null): string
    {
        return (string)$this->scopeConfig->getValue(
            SearchQuery::XML_PATH_MAX_QUERY_LENGTH,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Retrieve maximum query length
     *
     * @param mixed $store
     * @return string
     */
    public function getMinQueryLength($store = null): string
    {
        return (string)$this->scopeConfig->getValue(
            'amasty_xsearch/general/min_chars',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get search block positions
     *
     * @param string|int|null $store
     * @return array
     */
    public function getBlockPositions($store = null): array
    {
        $positionSort = function ($row) {
            return (int) $row;
        };

        $positions = array_map($positionSort, [
            'category' => $this->scopeConfig->getValue(
                'amasty_xsearch/category/position',
                ScopeInterface::SCOPE_STORE.
                $store
            ),
            'popularSearches' => $this->scopeConfig->getValue(
                'amasty_xsearch/popular_searches/position',
                ScopeInterface::SCOPE_STORE,
                $store
            ),
            'products' => $this->scopeConfig->getValue(
                'amasty_xsearch/product/position',
                ScopeInterface::SCOPE_STORE.
                $store
            ),
            'blog' => $this->scopeConfig->getValue(
                'amasty_xsearch/blog/position',
                ScopeInterface::SCOPE_STORE,
                $store
            ),
            'page' => $this->scopeConfig->getValue(
                'amasty_xsearch/page/position',
                ScopeInterface::SCOPE_STORE
            ),
            'faq' => $this->scopeConfig->getValue(
                'amasty_xsearch/faq/position',
                ScopeInterface::SCOPE_STORE,
                $store
            ),
            'brand' => $this->scopeConfig->getValue(
                'amasty_xsearch/brand/position',
                ScopeInterface::SCOPE_STORE,
                $store
            ),
            'landingPage' => $this->scopeConfig->getValue(
                'amasty_xsearch/landing_page/position',
                ScopeInterface::SCOPE_STORE,
                $store
            ),
            'recentSearches' => $this->scopeConfig->getValue(
                'amasty_xsearch/recent_searches/position',
                ScopeInterface::SCOPE_STORE,
                $store
            ),
            'browsingHistory' => $this->scopeConfig->getValue(
                'amasty_xsearch/browsing_history/position',
                ScopeInterface::SCOPE_STORE,
                $store
            ),
        ]);

        asort($positions, SORT_DESC);
        return $positions;
    }

    /**
     * Is recently viewed enabled
     *
     * @return bool
     */
    public function isRecentlyViewedEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            'amasty_xsearch/recently_viewed/enabled',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Is best sellers enabled
     *
     * @return bool
     */
    public function isBestsellersEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            'amasty_xsearch/bestsellers/enabled',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Is recently search button enabled
     *
     * @param null|int|string $store
     * @return bool
     */
    public function showSearchButton($store = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            'amasty_xsearch/general/display_search_button',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Is custom layout enabled
     *
     * @param null|int|string $store
     * @return bool
     */
    public function isCustomLayoutEnabled($store = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            'amasty_xsearch/layout/enabled',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Is recently search button enabled
     *
     * @param null|int|string $store
     * @return int
     */
    public function getSearchInputDelayInMS($store = null): int
    {
        $value = $this->scopeConfig->getValue(
            'amasty_xsearch/general/delay',
            ScopeInterface::SCOPE_STORE,
            $store
        ) ?? 0.7;

        return (int)((float)$value * 1000);
    }

    /**
     * Get product name max length
     *
     * @param null|int|string $store
     * @return int
     */
    public function getProductNameMaxLength($store = null): int
    {
        $value = $this->scopeConfig->getValue(
            'amasty_xsearch/product/name_length',
            ScopeInterface::SCOPE_STORE,
            $store
        ) ?? 50;

        return (int)$value;
    }

    /**
     * Is popup search width
     *
     * @param null|int|string $store
     * @return int
     */
    public function getPopupWidth($store = null): int
    {
        $value = $this->scopeConfig->getValue(
            'amasty_xsearch/general/popup_width',
            ScopeInterface::SCOPE_STORE,
            $store
        ) ?? 900;

        return (int)$value;
    }

    /**
     * Is popup search width
     *
     * @param null|int|string $store
     * @return bool
     */
    public function isDefaultSearchInput($store = null): bool
    {
        $value = $this->scopeConfig->getValue(
            'amasty_xsearch/general/dynamic_search_width',
            ScopeInterface::SCOPE_STORE,
            $store
        ) ?? 0;

        return (int)$value === 0;
    }

    /**
     * Is full width search
     *
     * @param null|int|string $store
     * @return bool
     */
    public function isFullWidthSearch($store = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            'amasty_xsearch/general/full_screen',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get header hyva ui config
     *
     * @param null|int|string $store
     * @return string
     */
    public function getHyvaUIHeaderVariant($store = null): string
    {
        $variant = $this->scopeConfig->getValue(
            'amasty_xsearch/hyva_themes/header_search_type',
            ScopeInterface::SCOPE_STORE,
            $store
        );

        return $variant ?? '';
    }

    /**
     * Check module compatibility with Amasty_AjaxCartHyva
     *
     * @return bool
     */
    public function isAmAjaxCartModuleEnable(): bool
    {
        return $this->moduleManager->isEnabled('Amasty_AjaxCartHyva');
    }

    /**
     * Check module compatibility with Amasty_AjaxCartHyva
     *
     * @return bool
     */
    public function isProductLabelEnabled(): bool
    {
        return $this->moduleManager->isEnabled('Amasty_ProductLabelAdvancedSearch');
    }

    /**
     * Get label alignment
     *
     * @param null|int|string $store
     */
    public function getLabelAlignment($store = null)
    {
        return $this->scopeConfig->getValue(
            'amasty_label/display/labels_alignment',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get label gap
     *
     * @param null|int|string $store
     *
     * @return string
     * */
    public function getLabelGap($store = null): string
    {
        return $this->scopeConfig->getValue(
            'amasty_label/display/margin_between',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param string $template
     * @param array $data
     * @param string $format
     * @return string
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function fetchView(
        string $template,
        array $data = [],
        string $format = 'Amasty_XsearchHyvaCompatibility::%s'
    ): string {
        $path = sprintf($format, $template);

        if (!empty($data)) {
            $this->template->assign($data);
        }

        return $this->template->fetchView($this->template->getTemplateFile($path));
    }

    /**
     * @param string $template
     * @param array $data
     * @return string
     * @throws ValidatorException
     */
    public function fetchPriceView(string $template, array $data = []): string
    {
        return $this->fetchView($template, $data);
    }
}
