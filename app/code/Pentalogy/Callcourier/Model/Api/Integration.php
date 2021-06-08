<?php
namespace Pentalogy\Callcourier\Model\Api;

use \Magento\Framework\App\Config\ScopeConfigInterface;
class Integration extends ApiAbstract
{
    const TRACKING_LINK = "http://cod.callcourier.com.pk/Booking/AfterSavePublic/";
    const GET_CITY_LIST_API_METHOD = 'GetCityList';
    const COURIER_CODE = "call_courier";
    const COURIER_TITLE = "Call Courier";
    const GO_TO  = "Go to 'STORES > Configuration > Pentalogy Extensions > Call Courier C.O.D' and Kindly Enter ";
    public function getApiCitiesList()
    {
        $response = $this->_doRequest(self::GET_CITY_LIST_API_METHOD);
        return (array)json_decode($response);
    }
    public function citiesToArray(){
        $data = array();
        $cities = $this->getApiCitiesList();
        $data[""] = "";
        foreach ($cities as $city){
            $data[$city->CityID]=$city->CityName;
        }
        return $data;
    }
    public function getShippingArea($cityId)
    {
        $method = "GetAreasByCity";
        $data['CityID'] = $cityId;
        $areaList = $this->_doRequest($method, $data);
        $areaList = json_decode($areaList);
        $shippingArea = array();
        foreach ($areaList as $area) {
            $shippingArea[$area->AreaID] = $area->AreaName;
        }
        return $shippingArea;
    }
    public function getServiceType(){
        $response = $this->_doRequest("GetServiceType/1");
        $arr = array_filter((array)json_decode($response), function ($index)
        {
            // returns if the input integer is even
            if($index->ServiceTypeID==1 || $index->ServiceTypeID==7)
                return true;
            else
                return false;
        });

        usort($arr,function ($a,$b)
        {
            if ($a->ServiceTypeID==$b->ServiceTypeID) return 0;
            return ($a->ServiceTypeID<$b->ServiceTypeID)?1:-1;
        });


        return $arr;
    }

    public function createShipment($shipmentData){
        $response = array();
        /*$shipmentData[''];*/
        $curlResponse = $this->_doRequest('SaveBooking',$shipmentData);
        $cnInformation = json_decode($curlResponse);
        if($cnInformation->Response == "true" ){
            $response['status'] = true;
            $response['consigneeNo'] = $cnInformation->CNNO;
            $response['message'] = 'Shipment is booked successfully';
        }else{
            $response['status'] = false;
            $response['consigneeNo'] = null;
            $response['message'] = 'Parameter(s) Error';
        }
        return $response;
    }
    public function tracking($cn){
        $data['cn'] = $cn;
        $curlResponse = $this->_doRequest('GetTackingHistory', $data);
        return (array) json_decode($curlResponse);
    }
    public function systemConfigurationValidator(){
        $errorMessage = null;
        if(empty($this->systemConfiguration['loginId'])){
            $errorMessage = "API KEY";
        }else if(empty($this->systemConfiguration['Origin'])){
            $errorMessage = "Origin";
        }else if(empty($this->systemConfiguration['ShipperName'])){
            $errorMessage = "Shipper Name";
        } else if (empty($this->systemConfiguration['ShipperArea'])){
            $errorMessage = "Shipper Area";
        }else if (empty($this->systemConfiguration['ShipperCity'])){
            $errorMessage = "Shipper City";
        }else if (empty($this->systemConfiguration['ShipperLandLineNo'])){
            $errorMessage = "Shipper Land Line Number";
        }else if (empty($this->systemConfiguration['ShipperCellNo'])){
            $errorMessage = "Shipper Cell Number";
        }else if(empty($this->systemConfiguration['ShipperEmail'])){
            $errorMessage = "Shipper Email";
        }else if(empty($this->systemConfiguration['ShipperAddress'])){
            $errorMessage = "Shipper Address";
        }
        else {
            return $errorMessage;
        }
        return self::GO_TO . " " .$errorMessage;
    }
    public function validateConsigneeCity($consigneeCity){
        $consigneeCity = strtoupper($consigneeCity);
        return array_search($consigneeCity,$this->citiesToArray());
    }

    public function getSystemConfigurationData(){
        return $this->systemConfiguration;
    }

    //Only for bulk shipment
    //@return Remarks string
    public function getRemarks(){
        return $this->_getSystemConfigValue(self::XML_SHIPPER_REMARKS);
    }

    public function getConfigServiceType(){
        return $this->_getSystemConfigValue(self::XML_SERVICE_TYPE);
    }

    public function getBeforeOrderStatus(){
        return $this->_getSystemConfigValue(self::XML_BEFORE_ORDER_STATUS);
    }

    //Only for bulk shipment
    public function bulkShipmentValidator(){
        $errorMessage = null;
        if(empty($this->getRemarks())){
            $errorMessage = "Remarks";
        }
        else if (empty($this->getConfigServiceType())){
            $errorMessage = "Service Type";
        }
        return $errorMessage;
    }
}