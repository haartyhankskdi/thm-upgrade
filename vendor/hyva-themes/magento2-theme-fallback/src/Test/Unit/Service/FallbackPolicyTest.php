<?php declare(strict_types=1);

/**
 * Hyvä Themes - https://hyva.io
 * Copyright © Hyvä Themes 2020-present. All rights reserved.
 * This product is licensed per Magento install
 * See https://hyva.io/license
 */

namespace Hyva\ThemeFallback\Test\Service;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\App\Request\Http;
use Hyva\ThemeFallback\Service\FallbackPolicy;

class FallbackPolicyTest extends TestCase
{
    /**
     * @var FallbackPolicy
     */
    private $fallbackPolicy;

    protected function setUp(): void
    {
        $this->fallbackPolicy = new FallbackPolicy();
    }

    /**
     * @dataProvider providerValidAction
     * @param string $url
     * @param string $configuredUrlSegment
     */
    public function testActionIsValid(string $url, string $configuredUrlSegment)
    {
        $this->assertTrue(
            $this->fallbackPolicy->isFallbackRequest(
                $this->getRequestMock($url),
                $configuredUrlSegment
            )
        );
    }

    /**
     * @dataProvider providerWrongAction
     * @param string $url
     * @param string $configuredUrlSegment
     */
    public function testActionIsWrong(string $url, string $configuredUrlSegment)
    {
        $this->assertFalse(
            $this->fallbackPolicy->isFallbackRequest(
                $this->getRequestMock($url),
                $configuredUrlSegment
            )
        );
    }

    /**
     * @dataProvider providerValidSegment
     * @param string $url
     * @param string $configuredUrlSegment
     */
    public function testSegmentIsValid(string $url, string $configuredUrlSegment)
    {
        $this->assertTrue(
            $this->fallbackPolicy->isFallbackRequest(
                $this->getRequestMock($url),
                $configuredUrlSegment
            )
        );
    }

    /**
     * @dataProvider providerWrongSegment
     * @param string $url
     * @param string $configuredUrlSegment
     */
    public function testSegmentIsWrong(string $url, string $configuredUrlSegment)
    {
        $this->assertFalse(
            $this->fallbackPolicy->isFallbackRequest(
                $this->getRequestMock($url),
                $configuredUrlSegment
            )
        );
    }

    /**
     * @return string[][]
     */
    public function providerValidAction(): array
    {
        return [
            'whole customer module is allowed' => ['/customer', 'customer'],
            'specific customer request is allowed' => ['/customer/account/login', 'customer'],
            'only login page is allowed' => ['/customer/account/login', 'customer/account/login'],
        ];
    }

    /**
     * @return string[][]
     */
    public function providerWrongAction(): array
    {
        return [
            'whole customer module is wrong' => ['/customer', 'checkout'],
            'specific customer request is wrong' => ['/customer/account/login', 'checkout/cart'],
            'specifiv action is wrong' => ['/customer/account/login', 'checkout/cart'],
        ];
    }

    /**
     * @return string[][]
     */
    public function providerValidSegment(): array
    {
        return [
            'specific demo product is allowed' => ['/category-1/product-demo.html', 'demo.html']
        ];
    }

    /**
     * @return string[][]
     */
    public function providerWrongSegment()
    {
        return [
            'specific url is wrong' => ['/category-1/product-demo.html', 'category.html']
        ];
    }

    /**
     * @param $url
     * @return Http|MockObject
     */
    public function getRequestMock($url): Http
    {
        $url = trim($url, '/');
        $routeParts = explode('/', $url, 3);

        $requestMock = $this->createMock(Http::class);

        $requestMock->method('getRouteName')
            ->willReturn(is_array($routeParts) && isset($routeParts[0]) ? $routeParts[0] : null);
        $requestMock->method('getControllerName')
            ->willReturn(is_array($routeParts) && isset($routeParts[1]) ? $routeParts[1] : null);
        $requestMock->method('getActionName')
            ->willReturn(is_array($routeParts) && isset($routeParts[2]) ? $routeParts[2] : null);
        $requestMock->method('getRequestUri')
            ->willReturn($url);

        return $requestMock;
    }
}
