<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search and Elastic Search GraphQL for Magento 2 (System)
 */

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\TestFramework\Helper\Bootstrap;

$registry = Bootstrap::getObjectManager()->get('Magento\Framework\Registry');

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var $attribute Attribute */
$attribute = Bootstrap::getObjectManager()->create('Magento\Catalog\Model\ResourceModel\Eav\Attribute');

$attribute->load('am_dropdown_attribute', 'attribute_code');
$attribute->delete();

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
