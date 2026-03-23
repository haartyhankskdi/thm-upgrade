<?php
 
namespace Haartyhanks\Hhcategory\Setup;
 
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\InstallDataInterface;
 
class InstallData implements InstallDataInterface
{
    private $eavSetupFactory;
 
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }
 
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
 
	    $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
 
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'home_mobile_category',
            [
                'type' => 'int',
                'label' => 'Home Mobile Category',
                'input' => 'boolean',
                'required' => false,
                'sort_order' => 4,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'wysiwyg_enabled' => true,
                'is_html_allowed_on_front' => true,
                'group' => 'General Information',
            ]
        );
 
        $setup->endSetup();
    }
}