<?php
namespace Infomodus\Dhllabel\Model\Config;
class Weight implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        $c = [
            ['label' => 'Pounds', 'value' => 'L'],
            ['label' => 'Kilograms', 'value' => 'K'],
        ];
        return $c;
    }
    public function getArray()
    {
        $array = [
            'L' =>'Pounds',
            'K' =>'Kilograms',
        ];
        return $array;
    }
}