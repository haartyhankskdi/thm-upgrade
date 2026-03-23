<?php declare(strict_types=1);

/**
 * Hyvä Themes - https://hyva.io
 * Copyright © Hyvä Themes 2020-present. All rights reserved.
 * This product is licensed per Magento install
 * See https://hyva.io/license
 */

namespace Hyva\ThemeFallback\Service;

use Magento\Framework\App\Request\Http;

class FallbackPolicy
{
    /**
     * @param Http $request
     * @param string $urlSegment
     * @return bool
     */
    public function isFallbackRequest(Http $request, string $urlSegment): bool
    {
        $isFallbackRequest = false;
        if (!$isFallbackRequest) {
            $isFallbackRequest = $this->isValidRouteControllerAction($request, $urlSegment);
        }
        if (!$isFallbackRequest) {
            $isFallbackRequest = $this->isValidSegment($request, $urlSegment);
        }

        return $isFallbackRequest;
    }

    /**
     * True if route, controller and action are valid
     *
     * @param Http $request
     * @param string $urlSegment
     * @return bool
     */
    private function isValidRouteControllerAction(Http $request, string $urlSegment): bool
    {
        $urlSegmentParts = explode('/', trim($urlSegment, '/'), 3);

        $isFallbackRequest = false;
        if (count($urlSegmentParts) === 1) {
            $isFallbackRequest = $this->isValidRouteName($request, $urlSegmentParts[0]);
        }
        if (count($urlSegmentParts) === 2) {
            $isFallbackRequest = $this->isValidRouteName($request, $urlSegmentParts[0])
                && $this->isValidControllerName($request, $urlSegmentParts[1]);
        }
        if (count($urlSegmentParts) === 3) {
            $isFallbackRequest = $this->isValidRouteName($request, $urlSegmentParts[0])
                && $this->isValidControllerName($request, $urlSegmentParts[1])
                && $this->isValidActionName($request, $urlSegmentParts[2]);
        }

        return $isFallbackRequest;
    }

    /**
     * @param Http $request
     * @param string $routeName
     * @return bool
     */
    private function isValidRouteName(Http $request, string $routeName): bool
    {
        return $routeName === $request->getRouteName();
    }

    /**
     * @param Http $request
     * @param string $controllerName
     * @return bool
     */
    private function isValidControllerName(Http $request, string $controllerName): bool
    {
        return $controllerName === $request->getControllerName();
    }

    /**
     * @param Http $request
     * @param string $actionName
     * @return bool
     */
    private function isValidActionName(Http $request, string $actionName): bool
    {
        return $actionName === $request->getActionName();
    }

    /**
     * The actual URI contains the part of configured url
     *
     * @param Http $request
     * @param string $urlSegment
     * @return bool
     */
    private function isValidSegment(Http $request, string $urlSegment): bool
    {
        $actualUrl = $request->getRequestUri();
        return strpos($actualUrl, $urlSegment) !== false;
    }
}
