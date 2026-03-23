<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_RewardPointsUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */
declare(strict_types=1);

namespace Mageplaza\RewardPointsUltimate\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Downloadable\Model\Product\Type as DownloadableType;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Mageplaza\RewardPointsUltimate\Model\Attribute\Backend\Pattern;
use Mageplaza\RewardPointsUltimate\Model\Config\Source\CustomerGroups;

/**
 * Class UpgradeProductAttr
 * @package Mageplaza\RewardPointsUltimate\Setup\Patch\Data
 */
class UpgradeProductAttr implements
    DataPatchInterface,
    PatchRevertableInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @var EavSetupFactory $eavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var $savePattern
     */
    private $savePattern = Pattern::class;

    /**
     * @var $typeBoolean
     */
    private $typeBoolean = Boolean::class;

    /**
     * UpgradeProductAttr constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        eavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $setup = $this->moduleDataSetup;
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $eavSetup->removeAttribute(Product::ENTITY, 'mp_rw_is_active');
        $eavSetup->addAttribute(
            Product::ENTITY,
            'mp_rw_is_active',
            array_merge(
                $this->getDFOptionsProduct(),
                [
                    'type'    => 'int',
                    'backend' => '',
                    'label'   => 'Enable',
                    'input'   => 'select',
                    'class'   => 'mp_rw_is_active',
                    'source'  => $this->typeBoolean,
                    'default' => 0
                ]
            )
        );

        $eavSetup->removeAttribute(Product::ENTITY, 'mp_reward_sell_product');
        $eavSetup->addAttribute(
            Product::ENTITY,
            'mp_reward_sell_product',
            array_merge(
                $this->getDFOptionsProduct(),
                [
                    'type'    => 'int',
                    'label'   => 'Reward points',
                    'input'   => 'text',
                    'class'   => '',
                    'source'  => '',
                    'default' => 0
                ]
            )
        );

        $eavSetup->removeAttribute(Product::ENTITY, 'mp_rw_customer_group');
        $eavSetup->addAttribute(
            Product::ENTITY,
            'mp_rw_customer_group',
            array_merge(
                $this->getDFOptionsProduct(),
                [
                    'type'    => 'text',
                    'backend' => $this->savePattern,
                    'label'   => 'Customer Group(s)',
                    'input'   => 'multiselect',
                    'class'   => 'mp_rw_customer_group',
                    'source'  => CustomerGroups::class,
                    'default' => null
                ]
            )
        );
    }

    /**
     * @return array
     */
    protected function getDFOptionsProduct()
    {
        $productTypes = [
            Type::TYPE_SIMPLE,
            Type::TYPE_VIRTUAL,
            DownloadableType::TYPE_DOWNLOADABLE,
            Configurable::TYPE_CODE
        ];

        return [
            'group'                   => 'Sell Product By Points',
            'global'                  => ScopedAttributeInterface::SCOPE_GLOBAL,
            'visible'                 => true,
            'required'                => false,
            'user_defined'            => false,
            'searchable'              => false,
            'filterable'              => false,
            'comparable'              => false,
            'visible_on_front'        => false,
            'used_in_product_listing' => true,
            'unique'                  => false,
            'frontend'                => '',
            'apply_to'                => join(',', $productTypes)
        ];
    }

    /**
     * @inheritdoc
     */
    public function revert()
    {
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @return string
     */
    public static function getVersion()
    {
        return '1.0.3';
    }
}
