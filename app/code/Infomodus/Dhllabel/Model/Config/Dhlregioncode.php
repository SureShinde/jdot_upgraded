<?php
namespace Infomodus\Dhllabel\Model\Config;

class Dhlregioncode implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        $c = array(
            array('label' => 'AP', 'value' => 'AP'),
            array('label' => 'EU', 'value' => 'EU'),
            array('label' => 'AM', 'value' => 'AM'),
        );
        return $c;
    }
}
