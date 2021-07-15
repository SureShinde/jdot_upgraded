<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Owner
 * Date: 16.12.11
 * Time: 10:55
 * To change this template use File | Settings | File Templates.
 */
namespace Infomodus\Dhllabel\Model\Config;

class PrintType implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['label' => __("PDF"), 'value' => "PDF"],
            ['label' => __("EPL2"), 'value' => "EPL2"],
            ['label' => __("ZPL2"), 'value' => "ZPL2"],
        ];
    }
}
