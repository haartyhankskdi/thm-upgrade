<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Amasty Shop By Brand GraphQl for Magento 2 (System)
 */

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Setup\CategorySetup;
use Magento\TestFramework\Helper\Bootstrap;

/** @var Attribute $attribute */
$attribute = Bootstrap::getObjectManager()->create(Attribute::class);

if (!$attribute->loadByCode(4, 'am_dropdown_attribute')->getId()) {

    /** @var CategorySetup $installer */
    $installer = Bootstrap::getObjectManager()->create(CategorySetup::class);

    $attribute->setData(
        [
            'attribute_code'                => 'am_dropdown_attribute',
            'entity_type_id'                => $installer->getEntityTypeId('catalog_product'),
            'is_global'                     => 0,
            'is_user_defined'               => 1,
            'frontend_input'                => 'select',
            'is_unique'                     => 0,
            'is_required'                   => 0,
            'is_searchable'                 => 0,
            'is_visible_in_advanced_search' => 0,
            'is_comparable'                 => 0,
            'is_filterable'                 => 1,
            'is_filterable_in_search'       => 0,
            'is_used_for_promo_rules'       => 0,
            'is_html_allowed_on_front'      => 1,
            'is_visible_on_front'           => 1,
            'used_in_product_listing'       => 1,
            'used_for_sort_by'              => 0,
            'frontend_label'                => ['Amasty Drop-Down Attribute'],
            'backend_type'                  => 'varchar',
            'option'                        => [
                'value' => [
                    'a_am_option_1' => ['A Amasty Option 1'],
                    'g_am_option_2' => ['G Amasty Option 2'],
                    'p_am_option_3' => ['P Amasty Option 3'],
                ],
                'order' => [
                    'a_am_option_1' => 1,
                    'g_am_option_2' => 2,
                    'p_am_option_3' => 3,
                ]
            ],
        ]
    );
    $attribute->save();

    $installer->addAttributeToGroup('catalog_product', 'Default', 'Attributes', $attribute->getId());
}
