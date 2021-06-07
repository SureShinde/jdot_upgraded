<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Owner
 * Date: 16.12.11
 * Time: 10:55
 * To change this template use File | Settings | File Templates.
 */
namespace Infomodus\Dhllabel\Model\Config;

class PrintFormatThermal implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['label' => '8X4_thermal', 'value' => '8X4_thermal'],
            ['label' => '8X4_CI_thermal', 'value' => '8X4_CI_thermal'],
            ['label' => '6X4_thermal', 'value' => '6X4_thermal'],
            ['label' => '8X4_CustBarCode_thermal', 'value' => '8X4_CustBarCode_thermal'],
        ];
    }
}
