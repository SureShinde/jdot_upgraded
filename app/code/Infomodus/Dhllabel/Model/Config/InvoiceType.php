<?php
namespace Infomodus\Dhllabel\Model\Config;
/**
 * Created by JetBrains PhpStorm.
 * User: Owner
 * Date: 16.12.11
 * Time: 10:55
 * To change this template use File | Settings | File Templates.
 */
class InvoiceType
{
    public function toOptionArray()
    {
        return [
            ['label' => 'Commercial', 'value' => 'CMI'],
            ['label' => 'Proforma', 'value' => 'PFI'],
        ];
    }
}