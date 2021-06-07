<?php
namespace Infomodus\Dhllabel\Model\Config;

class Referenceid implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        $c = [
            ['label' => 'No', 'value' => ''],
            /*array('label' => 'Shipment ID', 'value' => 'shipment'),*/
            ['label' => 'Order ID', 'value' => 'order'],
        ];
        return $c;
    }
}
