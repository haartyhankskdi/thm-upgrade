<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Model\Geoip;

use Amasty\Geoip\Model\Geolocation;
use Amasty\Storelocator\Model\ConfigProvider;
use Magento\Framework\HTTP\PhpEnvironment\Request;

class UserLocator
{
    public const KEY_LAT = 0;
    public const KEY_LNG = 1;

    /**
     * @var Geolocation
     */
    private $geolocation;

    /**
     * @var Request
     */
    private $httpRequest;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var array ['ip' => [0 => lat, 1 => lng], ...]
     */
    private $latLngCache = [];

    public function __construct(
        Geolocation $geolocation,
        Request $httpRequest,
        ConfigProvider $configProvider
    ) {
        $this->geolocation = $geolocation;
        $this->httpRequest = $httpRequest;
        $this->configProvider = $configProvider;
    }

    /**
     * @return array [0 => lat, 1 => lng]
     */
    public function getLatLng(): array
    {
        if (!$this->configProvider->getUseGeo()) {
            return [0, 0]; //default values for map
        }

        $ip = $this->httpRequest->getClientIp();
        if (!isset($this->latLngCache[$ip])) {
            $geoData = $this->geolocation->locate($ip);
            $lat = $geoData->getLatitude();
            $lng = $geoData->getLongitude();
            $this->latLngCache[$ip] = [
                self::KEY_LAT => $lat,
                self::KEY_LNG => $lng
            ];
        }

        return $this->latLngCache[$ip];
    }

    public function _resetState(): void
    {
        $this->latLngCache = [];
    }
}
