<?php
namespace Infomodus\Dhllabel\Model\Config;
class Unitofmeasurement implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        $array = [
            ['label' => 'Inches', 'value' => 'I'],
            ['label' => 'Centimeters', 'value' => 'C'],
        ];
        return $array;
    }
    public function getArray()
    {
        $array = [
            'I' =>'Inches',
            'C' =>'Centimeters',
        ];
        return $array;
    }
}
