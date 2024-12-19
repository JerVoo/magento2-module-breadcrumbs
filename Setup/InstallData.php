<?php
namespace JerVoo\Breadcrumbs\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

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

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'preferred_breadcrumb_category',
            [
                'type'         => 'int',
                'label'        => 'Preferred Breadcrumb Category',
                'input'        => 'boolean',
                'source'       => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                'visible'      => true,
                'default'      => '0',
                'required'     => false,
                'sort_order'   => 100,
                'global'       => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group'        => 'Search Engine Optimization',
            ]
        );


        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'preferred_breadcrumb_category',
            [
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Preferred Breadcrumb Category',
                'input' => 'select',
                'class' => '',
                'source' => \JerVoo\Breadcrumbs\Model\Attribute\Source\Category::class,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false,
                'apply_to' => '',
                'group' => 'Search Engine Optimization'
            ]
        );

        $setup->endSetup();
    }
}
