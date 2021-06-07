<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Owner
 * Date: 16.12.11
 * Time: 10:55
 * To change this template use File | Settings | File Templates.
 */
namespace Infomodus\Dhllabel\Model\Config;
class ProductAttributesMulti implements \Magento\Framework\Option\ArrayInterface
{
    protected $collectionAttributes;
    public function __construct(\Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $collectionAttributes)
    {
        $this->collectionAttributes = $collectionAttributes;
    }

    public function toOptionArray()
    {
        $coll = $this->collectionAttributes->create()->addFieldToFilter('entity_type_id', 4)->setOrder('main_table.frontend_label', 'ASC');
        $attributes = $coll->load()->getItems();
        $attributeArray = [[
            'label' => '',
            'value' => ''
        ]];

        foreach($attributes as $attribute){
            $attributeArray[] = [
                'label' => $attribute->getData('frontend_label'),
                'value' => $attribute->getData('attribute_code')
            ];
        }
        return $attributeArray;
    }
}