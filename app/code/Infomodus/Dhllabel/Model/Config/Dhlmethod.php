<?php
namespace Infomodus\Dhllabel\Model\Config;

class Dhlmethod implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        $c = [
            ['label' => __('Easy shop') . " " . __('(BTC, DOC)'), 'value' => '2'],
            ['label' => __('Sprintline') . " " . __('(SPL, DOC)'), 'value' => '5'],
            ['label' => __('Express easy') . " " . __('(XED, DOC)'), 'value' => '7'],
            ['label' => __('Europack') . " " . __('(EPA, DOC)'), 'value' => '9'],
            ['label' => __('Break bulk express') . " " . __('(BBX, DOC)'), 'value' => 'B'],
            ['label' => __('Medical express') . " " . __('(CMX, DOC)'), 'value' => 'C'],
            ['label' => __('Express worldwide') . " " . __('(DOX, DOC)'), 'value' => 'D'],
            ['label' => __('Express worldwide') . " " . __('(ECX, DOC)'), 'value' => 'U'],
            ['label' => __('Express 9:00') . " " . __('(TDK, DOC)'), 'value' => 'K'],
            ['label' => __('Express 10:30') . " " . __('(TDL, DOC)'), 'value' => 'L'],
            ['label' => __('Domestic economy select') . " " . __('(DES, DOC)'), 'value' => 'G'],
            ['label' => __('Economy select') . " " . __('(ESU, DOC)'), 'value' => 'W'],
            ['label' => __('Break bulk economy') . " " . __('(DOK, DOC)'), 'value' => 'I'],
            ['label' => __('Domestic express') . " " . __('(DOM, DOC)'), 'value' => 'N'],
            ['label' => __('Domestic express 10:30') . " " . __('(DOL, DOC)'), 'value' => 'O'],
            ['label' => __('Globalmail business') . " " . __('(GMB, DOC)'), 'value' => 'R'],
            ['label' => __('Same day') . " " . __('(SDX, DOC)'), 'value' => 'S'],
            ['label' => __('Express 12:00') . " " . __('(TDT, DOC)'), 'value' => 'T'],
            ['label' => __('Express envelope') . " " . __('(XPD, DOC)'), 'value' => 'X'],

            ['value' => '1', 'label' => __('Domestic express 12:00') . " " . __('(DOT, NON DOC)')],
            ['value' => '3', 'label' => __('Easy shop') . " " . __('(B2C, NON DOC)')],
            ['value' => '4', 'label' => __('Jetline') . " (" . __('(NFO, NON DOC)')],
            ['value' => '8', 'label' => __('Express easy') . " " . __('(XEP, NON DOC)')],
            ['value' => 'P', 'label' => __('Express worldwide') . " " . __('(WPX, NON DOC)')],
            ['value' => 'Q', 'label' => __('Medical express') . " " . __('(WMX, NON DOC)')],
            ['value' => 'E', 'label' => __('Express 9:00') . " " . __('(TDE, NON DOC)')],
            ['value' => 'F', 'label' => __('Freight worldwide') . " " . __('(FRT, NON DOC)')],
            ['value' => 'H', 'label' => __('Economy select') . " " . __('(ESI, NON DOC)')],
            ['value' => 'J', 'label' => __('Jumbo box') . " " . __('(JBX, NON DOC)')],
            ['value' => 'M', 'label' => __('Express 10:30') . " " . __('(TDM, NON DOC)')],
            ['value' => 'V', 'label' => __('Europack') . " " . __('(EPP, NON DOC)')],
            ['value' => 'Y', 'label' => __('Express 12:00') . " " . __('(TDY, NON DOC)')],
        ];
        return $c;
    }

    public function getDhlMethods()
    {
        $c = array();
        $arr = $this->toOptionArray();
        foreach ($arr as $val) {
            $c[$val['value']] = $val['label'];
        }
        return $c;
    }

    public function getDhlMethodName($code = '')
    {
        $c = array();
        $arr = $this->toOptionArray();
        foreach ($arr as $key => $val) {
            $c[$val['value']] = $val['label'];
        }
        if (array_key_exists($code, $c)) {
            return $c[$code];
        } else {
            return false;
        }
    }

    public function getMethodsByRequest($data, $type = 'global')
    {
        $methods = [];
        foreach ($this->getDhlMethods() as $k => $method) {
            $methods[$k] = $method . ((isset($data[$type]) && in_array($k, $data[$type])) ? __(' (Recommended)') : '');
        }
        return $methods;
    }


    public function getContentTypeByMethod($method)
    {
        $docType = array(
            '2' => 1,
            '5' => 1,
            '6' => 1,
            '7' => 1,
            '9' => 1,
            'B' => 1,
            'C' => 1,
            'D' => 1,
            'U' => 1,
            'K' => 1,
            'L' => 1,
            'G' => 1,
            'W' => 1,
            'I' => 1,
            'N' => 1,
            'O' => 1,
            'R' => 1,
            'S' => 1,
            'T' => 1,
            'X' => 1,
        );

        $nonDocType = array(
            '1' => 1,
            '3' => 1,
            '4' => 1,
            '8' => 1,
            'P' => 1,
            'Q' => 1,
            'E' => 1,
            'F' => 1,
            'H' => 1,
            'J' => 1,
            'M' => 1,
            'V' => 1,
            'Y' => 1,
        );

        if (array_key_exists($method, $docType)) {
            return "DOC";
        } elseif (array_key_exists($method, $nonDocType)) {
            return "NONDOC";
        } else {
            return false;
        }
    }
}
