<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Base for Magento 2
 */

namespace Amasty\Xsearch\Model;

class CustomSearchAttributes
{
    /**
     * @var array|null
     */
    private $attributes = null;

    /**
     * @var array
     */
    private $attributesConfig;

    public function __construct($attributesConfig = [])
    {
        $this->attributesConfig = $attributesConfig;
    }

    public function getAttributes(bool $userDefinedOnly = false): array
    {
        $this->initAttributes();
        if ($userDefinedOnly) {
            return array_filter($this->attributes, function (array $attributeConfig) {
                return $attributeConfig['user_defined'] ?? false;
            });
        }

        return $this->attributes;
    }

    private function initAttributes(): void
    {
        if ($this->attributes === null) {
            $this->attributes = [];
            foreach ($this->attributesConfig as $code => $config) {
                if (!isset($config['label'])) {
                    $config['label'] = $code;
                }
                /**
                 * @see \Amasty\Xsearch\Plugin\ElasticSearch\Model\QuerySettingsProcessor\AddCustomAttributeConfig
                 */
                if (!isset($config['wildcard'])) {
                    $config['wildcard'] = 0;
                }
                if (!isset($config['spelling'])) {
                    $config['spelling'] = 0;
                }
                if (!isset($config['combining'])) {
                    $config['combining'] = 0;
                }
                if (!isset($config['user_defined'])) {
                    $config['user_defined'] = false;
                }
                $this->attributes[$code] = $config;
            }
        }
    }

    public function clearInit(): void
    {
        $this->attributes = null;
    }
}
