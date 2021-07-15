<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Owner
 * Date: 16.12.11
 * Time: 10:55
 * To change this template use File | Settings | File Templates.
 */
namespace Infomodus\Dhllabel\Model\Config;

class PrintFormatPdf implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['label' => '8X4_A4_PDF', 'value' => '8X4_A4_PDF'],
            ['label' => '8X4_A4_TC_PDF', 'value' => '8X4_A4_TC_PDF'],
            ['label' => '8X4_CI_PDF', 'value' => '8X4_CI_PDF'],
            ['label' => '6X4_A4_PDF', 'value' => '6X4_A4_PDF'],
            ['label' => '8X4_RU_A4_PDF', 'value' => '8X4_RU_A4_PDF'],
            ['label' => '6X4_PDF', 'value' => '6X4_PDF'],
            ['label' => '8X4_PDF', 'value' => '8X4_PDF'],
            ['label' => '8X4_CustBarCode_PDF', 'value' => '8X4_CustBarCode_PDF'],
            ['label' => '8X4_thermal', 'value' => '8X4_thermal'],
            ['label' => '8X4_CI_thermal', 'value' => '8X4_CI_thermal'],
            ['label' => '6X4_thermal', 'value' => '6X4_thermal'],
            ['label' => '8X4_CustBarCode_thermal', 'value' => '8X4_CustBarCode_thermal'],
        ];
    }
}
