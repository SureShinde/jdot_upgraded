<?php

namespace Mean3\Stallionshipping\Model\Config\Source;

class ChargeType implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => '1', 'label' => __('OverLand')],
            ['value' => '2', 'label' => __('OverNight')]
        ];
    }
}
