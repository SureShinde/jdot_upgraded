<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\OgTags\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Setup\EavSetup;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;

/**
 * Class InstallData
 * @package Magefan\OgTags\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;

    /**
     * @var CategorySetupFactory
     */
    private $categorySetupFactory;

    /**
     * InstallData constructor.
     * @param EavSetupFactory $eavSetupFactory
     * @param AttributeSetFactory $attributeSetFactory
     * @param CategorySetupFactory $categorySetupFactory
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        AttributeSetFactory $attributeSetFactory,
        CategorySetupFactory $categorySetupFactory
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->categorySetupFactory = $categorySetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {

        if (version_compare($context->getVersion(), '2.0.4') < 0) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'magefan_og_title',
                [
                    'type' => 'varchar',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'OG Title',
                    'input' => 'text',
                    'class' => '',
                    'source' => '',
                    'global' => 0,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'default' => null,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'unique' => false,
                    'apply_to' => '',
                    'system' => 1,
                    'group' => 'Open Graph Metadata',
                    'option' => ['values' => [""]]
                ]
            );

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'magefan_og_description',
                [
                    'type' => 'varchar',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'OG Description',
                    'input' => 'text',
                    'class' => '',
                    'source' => '',
                    'global' => 0,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'default' => null,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'unique' => false,
                    'apply_to' => '',
                    'system' => 1,
                    'group' => 'Open Graph Metadata',
                    'option' => ['values' => [""]]
                ]
            );

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'magefan_og_image',
                [
                    'type' => 'varchar',
                    'backend' => '',
                    'frontend' => 'Magento\Catalog\Model\Product\Attribute\Frontend\Image',
                    'label' => 'OG Image',
                    'input' => 'media_image',
                    'class' => '',
                    'source' => '',
                    'global' => 0,
                    'group' => 'Open Graph Metadata',
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'default' => null,
                ]
            );

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Category::ENTITY,
                'magefan_og_image',
                [
                    'type' => 'varchar',
                    'label' => 'OG Image',
                    'input' => 'image',
                    'sort_order' => 3,
                    'source' => '',
                    'global' => 0,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => false,
                    'default' => null,
                    'group' => 'Open Graph Metadata',
                    'backend' => 'Magento\Catalog\Model\Category\Attribute\Backend\Image'
                ]
            );

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Category::ENTITY,
                'magefan_og_title',
                [
                    'type' => 'varchar',
                    'label' => 'OG Title',
                    'input' => 'text',
                    'sort_order' => 1,
                    'source' => '',
                    'global' => 0,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => false,
                    'default' => null,
                    'group' => 'Open Graph Metadata',
                    'backend' => ''
                ]
            );

            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Category::ENTITY,
                'magefan_og_description',
                [
                    'type' => 'varchar',
                    'label' => 'OG Description',
                    'input' => 'text',
                    'sort_order' => 2,
                    'source' => '',
                    'global' => 0,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => false,
                    'default' => null,
                    'group' => 'Open Graph Metadata',
                    'backend' => ''
                ]
            );

            /* Add product OG image to the Images attribute group */
            $entityTypeId = $eavSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
            $attributeSetIds = $eavSetup->getAllAttributeSetIds($entityTypeId);

            $attributeGroupCode = 'image-management';
            foreach ($attributeSetIds as $attributeSetId) {
                $attributeGroupId = $eavSetup->getAttributeGroup($entityTypeId, $attributeSetId, $attributeGroupCode, 'attribute_group_id');
                $attributeId = $eavSetup->getAttributeId($entityTypeId, 'magefan_og_image');
                $attributeGroupId = $eavSetup->getAttributeGroupId($entityTypeId, $attributeSetId, $attributeGroupId);
                $eavSetup->addAttributeToGroup($entityTypeId, $attributeSetId, $attributeGroupId, $attributeId, null);
            }
        }
    }
}
