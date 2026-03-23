<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Model;

use Amasty\Storelocator\Helper\Data;
use Amasty\Storelocator\Model\ConfigHtmlConverter\VariablesRendererInterface;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class ConfigHtmlConverter
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var FilterProvider
     */
    private $filterProvider;

    /**
     * @var CountryFactory
     */
    private $countryFactory;

    /**
     * @var RegionFactory
     */
    private $regionFactory;

    /**
     * @var Location
     */
    private $location;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var BaseImageLocation
     */
    private $baseImageLocation;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \Amasty\Storelocator\Helper\Data
     */
    private $dataHelper;

    /**
     * @var VariablesRendererInterface[]
     */
    private $variableRenderers;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var array
     */
    private $countryNameByCode = [];

    /**
     * @var array
     */
    private $stateNameByCode = [];

    public function __construct(
        ConfigProvider $configProvider,
        Escaper $escaper,
        FilterProvider $filterProvider,
        LoggerInterface $logger,
        CountryFactory $countryFactory,
        RegionFactory $regionFactory,
        UrlInterface $urlBuilder,
        BaseImageLocation $baseImageLocation,
        Data $dataHelper,
        array $variableRenderers = [],
        ?StoreManagerInterface $storeManager = null // TODO move to not optional
    ) {
        $this->configProvider = $configProvider;
        $this->escaper = $escaper;
        $this->filterProvider = $filterProvider;
        $this->countryFactory = $countryFactory;
        $this->regionFactory = $regionFactory;
        $this->urlBuilder = $urlBuilder;
        $this->baseImageLocation = $baseImageLocation;
        $this->logger = $logger;
        $this->dataHelper = $dataHelper;
        $this->variableRenderers = $variableRenderers;
        $this->storeManager = $storeManager ?? ObjectManager::getInstance()->get(StoreManagerInterface::class);
    }

    /**
     * @param Location $location
     */
    public function setHtml($location)
    {
        $this->location = $location;
        $this->location->setPhoto($this->baseImageLocation->getMainImageUrl($location));
        try {
            $this->location->setStoreListHtml($this->dataHelper->compressHtml($this->getStoreListHtml()));
            $this->location->setPopupHtml($this->dataHelper->compressHtml($this->getPopupHtml()));
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }

    /**
     * Fix Amasty Store Pickup With Locator invalid template setting
     */
    public function setDescriptionHtml(Location $location): void
    {
        $this->location = $location;
        try {
            $this->location->setDescriptionHtml($this->dataHelper->compressHtml($this->getStoreDetailsHtml()));
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }

    /**
     * Get store list html
     */
    private function getStoreListHtml()
    {
        $storeListTemplate = $this->configProvider->getStoreListTemplate();

        return $this->replaceLocationValues($storeListTemplate);
    }

    /**
     * Get popup html
     */
    private function getPopupHtml()
    {
        $baloon = $this->configProvider->getLocatorTemplate();

        return $this->replaceLocationValues($baloon);
    }

    private function getStoreDetailsHtml(): string
    {
        $storeDetailsTemplate = $this->configProvider->getStoreTemplate();

        return $this->replaceLocationValues($storeDetailsTemplate);
    }

    /**
     * Return html with replaced values
     *
     * @todo improve logic
     * @param string $template
     * @return string $html
     */
    private function replaceLocationValues($template)
    {
        $locationData = $this->location->getData();
        $template = preg_replace_callback(
            '/{{if(?\'if\'.*)}}(.*|\n)*?{{\/if(?P=if)}}/mixU',
            function ($match) use ($locationData) {
                if (!empty($locationData[$match['if']])) {
                    $value = $this->getPreparedValue($match['if']);

                    return str_replace(
                        [
                            '{{' . $match['if'] . '}}',
                            '{{if' . $match['if'] . '}}',
                            '{{/if' . $match['if'] . '}}'
                        ],
                        [$value, '', ''],
                        $match['0']
                    );
                }

                return '';
            },
            $template
        );

        $html = preg_replace_callback(
            '/{{(.*)}}/miU',
            function ($match) use ($locationData) {
                $renderer = $this->variableRenderers[$match['1']] ?? null;

                if ($renderer instanceof VariablesRendererInterface) {
                    return $renderer->renderVariable($this->location, $match['1']);
                }

                if (isset($locationData[$match['1']]) || isset($locationData['attributes'][$match['1']])) {
                    if (isset($locationData['attributes'][$match['1']])) {
                        return $this->convertAttributeData($locationData['attributes'][$match['1']]);
                    }

                    return $this->getPreparedValue($match['1']);
                } else {
                    return '';
                }
            },
            $template
        );

        return $html;
    }

    /**
     * Get prepared value by key
     *
     * @param string $key
     * @return string
     */
    private function getPreparedValue($key)
    {
        $preparedKey = 'prepared_' . $key;
        if (!$this->location->hasData($preparedKey)) {
            $this->location->setData($preparedKey, $this->prepareValue($key));
        }
        return $this->location->getData($preparedKey);
    }

    /**
     * @param string $key
     * @return string
     */
    private function prepareValue($key)
    {
        switch ($key) {
            case 'name':
                if ($this->location->getUrlKey() && $this->configProvider->getEnablePages()) {
                    return '<div class="amlocator-title"><a class="amlocator-link" href="' . $this->getLocationUrl()
                        . '" title="' . $this->escaper->escapeHtml($this->location->getData($key))
                        . '" target="_blank">'
                        . $this->escaper->escapeHtml($this->location->getData($key)) . '</a>'
                        . $this->getSvgImageContainer() . '</div>';
                }

                return '<div class="amlocator-title">' . $this->escaper->escapeHtml($this->location->getData($key))
                    . '</div>';
            case 'description':
                return $this->getPreparedDescription($key, true);
            case 'short_description':
                return $this->getPreparedDescription($key);
            case 'country':
                return $this->escaper->escapeHtml($this->getCountryName());
            case 'state':
                return $this->escaper->escapeHtml($this->getStateName());
            case 'rating':
                return $this->location->getData($key);
            case 'photo':
                $photo = $this->location->getData($key);

                if (empty($photo)) {
                    return '';
                }

                return '<div class="amlocator-image"><img alt="'
                    . $this->escaper->escapeHtmlAttr($this->location->getName())
                    .'" src="' . $this->escaper->escapeUrl($photo) . '"></div>';
            default:
                return $this->escaper->escapeHtml($this->location->getData($key));
        }
    }

    // phpcs:disable
    private function getSvgImageContainer(): string
    {
        return '<div class="amlocator-map-pin" data-amlocator-js="map-pin-icon" tabindex="0" role="button" aria-label="'
            . $this->escaper->escapeHtmlAttr(__('Press Enter to show store on the map'))
            . '">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        stroke-width="2"
                        stroke="currentColor"
                        fill="none"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        aria-hidden="true">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M12 18.5l-3 -1.5l-6 3v-13l6 -3l6 3l6 -3v7" />
                        <path d="M9 4v13" />
                        <path d="M15 7v5" />
                        <path d="M21.121 20.121a3 3 0 1 0 -4.242 0c.418 .419 1.125 1.045 2.121 1.879c1.051 -.89 1.759 -1.516 2.121 -1.879z" />
                        <path d="M19 18v.01" />
                    </svg>
                </div>';
    }
    // phpcs:enable

    /**
     * Get prepared description
     *
     * @return string
     */
    public function getPreparedDescription($key, bool $useFilterProcessor = false)
    {
        $descriptionLimit = $this->configProvider->getDescriptionLimit();

        $description = $this->location->getData($key);
        if ($useFilterProcessor) {
            $description = $this->filterProvider->getPageFilter()->filter($description);
        }
        $description = strip_tags(
            preg_replace('#(<style.*?>).*?(</style>)#', '$1$2', $description)
        );
        if (strlen($description) < $descriptionLimit) {
            return '<div class="amlocator-description">' . $description . '</div>';
        }

        if ($descriptionLimit) {
            if (preg_match('/^(.{' . ($descriptionLimit) . '}.*?)\b/isu', $description, $matches)) {
                $description = $matches[1] . '...';
            }

            if ($this->configProvider->getEnablePages()) {
                $description .= '<a href="' . $this->getLocationUrl() . '" title="read more" target="_blank"> '
                    . __('Read More') . '</a>';
            }
        }

        return '<div class="amlocator-description">' . $description . '</div>';
    }

    /**
     * Convert attributes data to html
     *
     * @param array $attributeData
     *
     * @return string $html
     */
    private function convertAttributeData($attributeData)
    {
        $html = $this->escaper->escapeHtml($attributeData['frontend_label']) . ':<br>';
        if (isset($attributeData['option_title']) && is_array($attributeData['option_title'])) {
            foreach ($attributeData['option_title'] as $option) {
                $html .= '- ' . $this->escaper->escapeHtml($option) . '<br>';
            }
            return $html;
        } else {
            $value = isset($attributeData['option_title']) ? $attributeData['option_title'] : $attributeData['value'];

            return $html . $this->escaper->escapeHtml($value) . '<br>';
        }
    }

    /**
     * Get country name
     *
     * @return string
     */
    private function getCountryName()
    {
        if (!isset($this->countryNameByCode[$this->location->getCountry()])) {
            $this->countryNameByCode[$this->location->getCountry()] = $this->countryFactory->create()->loadByCode(
                $this->location->getCountry()
            )->getName();
        }
        return $this->countryNameByCode[$this->location->getCountry()];
    }

    /**
     * Get state name
     *
     * @return string
     */
    private function getStateName()
    {
        if (!isset($this->stateNameByCode[$this->location->getState()])) {
            $stateName = $this->regionFactory->create()->load($this->location->getState())->getName();
            $this->stateNameByCode[$this->location->getState()] = $stateName ?: $this->location->getState();
        }
        return $this->stateNameByCode[$this->location->getState()];
    }

    /**
     * Get location url
     *
     * @return string
     */
    private function getLocationUrl()
    {
        return $this->escaper->escapeUrl(
            $this->urlBuilder->getUrl(
                $this->configProvider->getUrl() . '/' . $this->location->getUrlKey(),
                ['_scope' => $this->storeManager->getStore()->getId()]
            )
        );
    }

    public function _resetState(): void
    {
        $this->stateNameByCode = [];
        $this->countryNameByCode = [];
    }
}
