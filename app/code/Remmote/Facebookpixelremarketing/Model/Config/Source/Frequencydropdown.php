<?php
/**
 * @extension   Remmote_Facebookpixelremarketing
 * @author      Remmote
 * @copyright   2017 - Remmote.com
 * @descripion  Frecuency dropdown
 */
namespace Remmote\Facebookpixelremarketing\Model\Config\Source;

class Frequencydropdown implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Frequency dropdown
     * @return [type]
     * @author Remmote
     * @date   2017-06-12
     */
    public function toOptionArray()
    {
        return [
            ['value' => '1', 'label' => 'Daily'],
            ['value' => '7', 'label' => 'Weekly']
        ];
    }
}