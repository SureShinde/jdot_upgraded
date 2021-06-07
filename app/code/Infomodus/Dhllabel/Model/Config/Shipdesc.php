<?php
namespace Infomodus\Dhllabel\Model\Config;

class Shipdesc implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        $c = [
            ['label' => __('Customer name + Order Id'), 'value' => '1'],
            ['label' => __('Only Customer name'), 'value' => '2'],
            ['label' => __('Only Order Id'), 'value' => '3'],
            ['label' => __('List of Products'), 'value' => '4'],
            ['label' => __('Custom value'), 'value' => '5'],
            ['label' => __('nothing'), 'value' => ''],
        ];
        return $c;
    }
}
