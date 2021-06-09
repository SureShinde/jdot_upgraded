<?php
namespace RLTSquare\CancelOrder\Model\Config\Source;

class InventoryStatus implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 1, 'label' => __('Yes i want to restock inventory')],
            ['value' => 0, 'label' => __('No i dont want to restock inventory')]
        ];
    }
}