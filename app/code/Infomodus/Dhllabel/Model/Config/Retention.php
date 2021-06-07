<?php
namespace Infomodus\Dhllabel\Model\Config;
class Retention implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        $c = [
            ['label' => 'Not used', 'value' => ''],
            ['label' => '3 months', 'value' => 'PT'],
            ['label' => '6 months', 'value' => 'PU'],
        ];
        return $c;
    }
}