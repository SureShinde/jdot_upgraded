<?php

namespace Pentalogy\Callcourier\Model\Config\Source;
use Pentalogy\Callcourier\Model\Api;
class Servicetype implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray(){
        $services = (new Api\Integration())->getServiceType();;
        $data = array(''=>'--Please Select--');
        foreach ($services as $value){
            $data[$value->ServiceTypeID] = $value->ServiceType1;
        }
        return $data;
    }
}