<?php
namespace Infomodus\Dhllabel\Model\Config;
/**
 * Created by JetBrains PhpStorm.
 * User: Owner
 * Date: 16.12.11
 * Time: 10:55
 * To change this template use File | Settings | File Templates.
 */
class TermsOfTrade
{
    public function toOptionArray()
    {
        $c = [
            ['label' => 'Ex Works', 'value' => 'EXW'],
            ['label' => 'Free Carrier', 'value' => 'FCA'],
            ['label' => 'Carriage Paid To', 'value' => 'CPT'],
            ['label' => 'Carriage and Insurance Paid To', 'value' => 'CIP'],
            ['label' => 'Delivered At Terminal', 'value' => 'DAT'],
            ['label' => 'Delivered At Place', 'value' => 'DAP'],
            ['label' => 'Delivered Duty Paid', 'value' => 'DDP'],
            ['label' => 'Cost and freight', 'value' => 'CFR'],
            ['label' => 'Free alongside Ship', 'value' => 'FAS'],
            ['label' => 'Free on Board', 'value' => 'FOB'],
        ];
        return $c;
    }
}