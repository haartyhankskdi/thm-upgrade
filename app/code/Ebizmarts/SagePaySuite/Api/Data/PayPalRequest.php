<?php

namespace Ebizmarts\SagePaySuite\Api\Data;

use Magento\Framework\Model\AbstractExtensibleModel;

class PayPalRequest extends AbstractExtensibleModel implements PayPalRequestInterface
{
    /**
     * @return int
     */
    public function getJavascriptEnabled(): int
    {
        return $this->getData(self::JS_ENABLED);
    }

    /**
     * Boolean that represents the ability of the cardholder browser to execute JavaScript.
     * @param int $enabled
     * @return void
     */
    public function setJavascriptEnabled(int $enabled): void
    {
        $this->setData(self::JS_ENABLED, $enabled);
    }

    /**
     * @return string
     */
    public function getAcceptHeaders(): string
    {
        return $this->getData(self::ACCEPT_HEADERS);
    }

    /**
     * Exact content of the HTTP accept headers as sent to the 3DS Requestor from the Cardholder’s browser.
     * @param string $headers
     * @return void
     */
    public function setAcceptHeaders(string $headers): void
    {
        $this->setData(self::ACCEPT_HEADERS, $headers);
    }

    /**
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->getData(self::LANGUAGE);
    }

    /**
     * Value representing the browser language as defined in IETF BCP47. Returned from navigator.language property.
     * @param string $language
     * @return void
     */
    public function setLanguage(string $language): void
    {
        $this->setData(self::LANGUAGE, $language);
    }

    /**
     * @return string
     */
    public function getUserAgent(): string
    {
        return $this->getData(self::USER_AGENT);
    }

    /**
     * Exact content of the HTTP user-agent header.
     * @param string $userAgent
     * @return void
     */
    public function setUserAgent(string $userAgent): void
    {
        $this->setData(self::USER_AGENT, $userAgent);
    }

    /**
     * @return int
     */
    public function getJavaEnabled(): int
    {
        return $this->getData(self::JAVA_ENABLED);
    }

    /**
     * Boolean that represents the ability of the cardholder browser to execute Java.
     * @param int $javaEnabled
     * @return void
     */
    public function setJavaEnabled(int $javaEnabled): void
    {
        $this->setData(self::JAVA_ENABLED, $javaEnabled);
    }

    /**
     * @return int
     */
    public function getColorDepth(): int
    {
        return $this->getData(self::COLOR_DEPTH);
    }

    /**
     * Exact content of the HTTP user-agent header.
     * @param int $colorDepth
     * @return void
     */
    public function setColorDepth(int $colorDepth): void
    {
        $this->setData(self::COLOR_DEPTH, $colorDepth);
    }

    /**
     * @return int
     */
    public function getScreenWidth(): int
    {
        return $this->getData(self::SCREEN_WIDTH);
    }

    /**
     * Exact content of the HTTP user-agent header.
     * @param int $screenWidth
     * @return void
     */
    public function setScreenWidth(int $screenWidth): void
    {
        $this->setData(self::SCREEN_WIDTH, $screenWidth);
    }

    /**
     * @return int
     */
    public function getScreenHeight(): int
    {
        return $this->getData(self::SCREEN_HEIGHT);
    }

    /**
     * Exact content of the HTTP user-agent header.
     * @param int $screenHeight
     * @return void
     */
    public function setScreenHeight(int $screenHeight): void
    {
        $this->setData(self::SCREEN_HEIGHT, $screenHeight);
    }

    /**
     * @return int
     */
    public function getTimezone(): int
    {
        return $this->getData(self::TIMEZONE);
    }

    /**
     * Exact content of the HTTP user-agent header.
     * @param int $timezone
     * @return void
     */
    public function setTimezone(int $timezone): void
    {
        $this->setData(self::TIMEZONE, $timezone);
    }
    public function __toArray(): array
    {
        return [
            self::JS_ENABLED => $this->getJavaEnabled(),
            self::ACCEPT_HEADERS => $this->getAcceptHeaders(),
            self::LANGUAGE => $this->getLanguage(),
            self::USER_AGENT => $this->getUserAgent(),
            self::JAVA_ENABLED => $this->getJavaEnabled(),
            self::COLOR_DEPTH => $this->getColorDepth(),
            self::SCREEN_WIDTH => $this->getScreenWidth(),
            self::SCREEN_HEIGHT => $this->getScreenHeight(),
            self::TIMEZONE => $this->getTimezone()
        ];
    }
}
