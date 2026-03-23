<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Microsoft Consent Mode
 */

namespace Amasty\MicrosoftConsentMode\Model\Config\Backend\Serialized;

use Magento\Config\Model\Config\Backend\Serialized\ArraySerialized;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;

class ConsentTypesArraySerialized extends ArraySerialized
{
    public function __construct(
        private readonly Json $serializer,
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    public function beforeSave(): ConsentTypesArraySerialized
    {
        if (!is_array($this->getValue()) || empty(array_filter($this->getValue()))) {
            $this->setValue($this->getOldValue());
        }

        return parent::beforeSave();
    }

    public function getOldValue(): string
    {
        // If the value is retrieved from defaults defined in config.xml
        // it may be returned as an array.
        $value = $this->_config->getValue(
            $this->getPath(),
            $this->getScope() ?: ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $this->getScopeCode()
        );

        if (is_array($value)) {
            return $this->serializer->serialize($value);
        }

        return (string)$value;
    }
}
