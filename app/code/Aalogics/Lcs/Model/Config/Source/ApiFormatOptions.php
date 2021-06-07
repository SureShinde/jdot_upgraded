<?php

namespace Aalogics\Lcs\Model\Config\Source;

class ApiFormatOptions implements \Magento\Framework\Option\ArrayInterface
{
    /*
     * Option getter
     * @return array
     */
    public function toOptionArray()
    {
        $arr = $this->_getApiformatOptions();
        $ret = [];

        foreach ($arr as $key => $value)
        {
            $ret[] = [
                'value' => $key,
                'label' => $value
            ];
        }

        return $ret;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function _getApiformatOptions()
    {
        return [
            'xml' => __('XML'), 
            'json' => __('JSON')
        ];
    }
}