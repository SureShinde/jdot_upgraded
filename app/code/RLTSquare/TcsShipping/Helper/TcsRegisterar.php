<?php
/**
 * NOTICE OF LICENSE
 * You may not sell, distribute, sub-license, rent, lease or lend complete or portion of software to anyone.
 *
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future.
 *
 * This file is going to be edited because SOAP Api is not usable by TCS, We're changing it to REST
 * REST creation date: 28-12-2020 - MONDAY.
 * @author Salman Hanif, salman.hanif@rltsquare.com
 *
 * @package   RLTSquare_TcsShipping
 * @copyright Copyright (c) 2018 RLTSquare (https://www.rltsquare.com)
 * @contacts  support@rltsquare.com
 * @license  See the LICENSE.md file in module root directory
 */

namespace RLTSquare\TcsShipping\Helper;

/**
 * Class TcsRegisterar
 * @package RLTSquare\TcsShipping\Helper
 * @author Umar Chaudhry <umarch@rltsquare.com>
 * @edited Salman Hanif <salman.hanif@rltsquare.com>
 */
class TcsRegisterar
{
    const REST_URL = "https://apis.tcscourier.com/production/v1/cod/create-order";
    const HOST = "https://apis.tcscourier.com";
    const ENDPOINT = "https://apis.tcscourier.com/production";
    const CLIENT_ID = "8b3908d1-a71a-4439-bf79-4ebc1d45a1c0";

    /**
     * @var
     */
    protected $_order;

    /**
     * @var \RLTSquare\TcsShipping\Model\Carrier
     */
    protected $_tcsShippingModel;

    /**
     * @var
     */
    protected $_customWeight;

    /**
     * TcsRegisterar constructor.
     * @param \RLTSquare\TcsShipping\Model\Carrier $tcsShipping
     */
    public function __construct(
        \RLTSquare\TcsShipping\Model\Carrier $tcsShipping
    )
    {
        $this->_tcsShippingModel = $tcsShipping;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return string
     * this is the main function of sending API and getting consigment number
     */
    public function shipOrder(\Magento\Sales\Model\Order $order, $customWeight = null)
    {
        $this->_order = $order;

        if( $customWeight != null ) {
            $this->_customWeight = $customWeight;
        }

        $postData = $this->getPostRestData();
        $headers = $this->getRestHeadersArray();

        $url = self::REST_URL;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        curl_close($ch);

        $consignmentNo = $this->getConsignmentNo($response);
        return $consignmentNo;
    }

    /**
     * @return false|string
     * this function get required data from order and set in the CURL post data
     */
    private function getPostRestData()
    {
        $username = $this->_tcsShippingModel->getConfigData('username');
        $password = $this->_tcsShippingModel->getConfigData('password');
        $costCenterCode = $this->_tcsShippingModel->getConfigData('costCenterCode');
        $originCityName = $this->_tcsShippingModel->getConfigData('originCityName');
        $isFragile = $this->_tcsShippingModel->getConfigData('isFragile') == "1" ? "YES" : "NO";

        $serviceCode = $this->_tcsShippingModel->getConfigData('serviceCode');
        $insuranceValue = $this->_tcsShippingModel->getConfigData('insuranceValue');
        $statusHistoryItem = $this->_order->getStatusHistoryCollection()->getFirstItem();
        $comment = $statusHistoryItem->getComment();

        $consigneeName = $this->_order->getShippingAddress()->getName();

        $address1 = null;
        $address2 = null;
        try {
            $address1 = $this->_order->getShippingAddress()->getStreet()[0];
            $address2 = $this->_order->getShippingAddress()->getStreet()[1];
        } catch (\Exception $e) {
        }

        if ($address1 && $address2)
            $consigneeAddress = $address1 . ' ' . $address2;
        else
            $consigneeAddress = $address1;

        $consigneePhone = $this->_order->getShippingAddress()->getTelephone();
        $consigneeEmail = $this->_order->getShippingAddress()->getEmail();
        $destinationCity = $this->_order->getShippingAddress()->getCity();
        $customerReferenceNo = $this->_order->getIncrementId();

        $totalItemsOrdered = ceil($this->_order->getTotalQtyOrdered());

        if($this->_customWeight > 0){
            $orderPackageWeight = $this->_customWeight;
        }else{
            $orderPackageWeight = $this->_order->getWeight();
        }


        $cod = $this->_order->getBaseGrandTotal();

        $postData = array(
            "userName" =>  $username,
            "password" => $password,
            "costCenterCode" => $costCenterCode,
            "consigneeName" => $consigneeName,
            "consigneeAddress" => $consigneeAddress,
            "consigneeMobNo" => $consigneePhone,
            "consigneeEmail" => $consigneeEmail,
            "originCityName" => $originCityName,
            "destinationCityName" => $destinationCity,
            "weight" => $orderPackageWeight,
            "pieces" => $totalItemsOrdered,
            "codAmount" => $cod,
            "customerReferenceNo" => $customerReferenceNo,
            "services" => $serviceCode,
            "productDetails" => "",
            "fragile" => $isFragile,
            "remarks" => "",
            "insuranceValue" => $insuranceValue
        );
        $postData = json_encode($postData);
        return $postData;
    }

    /**
     * @return string[]
     * this function set the curl Headers with JunaidJamshed TCS client ID
     */
    private function getRestHeadersArray()
    {
        $headers = array(
            "X-IBM-Client-Id:" . self::CLIENT_ID,
            "accept: application/json",
            "Cache-Control: no-cache",
            "content-type: application/json",
        );
        return $headers;
    }

    /**
     * @param $curlResponse
     * @return false|string|string[]
     * this function retrieve consignment number from json response but this will work only if
     * the consignment number is only integer number,
     * TODO: In case TCS Change their consignment numbers from integer to some Alphanumeric strings,
     * then we will modify this function
     */
    private function getConsignmentNo($curlResponse)
    {
        if(isset($curlResponse)) {
            if (json_decode($curlResponse)->returnStatus->status == "SUCCESS") {
                $theConsignmentMessageStr = json_decode($curlResponse)->bookingReply->result;
                $consignmentIntRetriever = preg_replace('/[^0-9]/', '', $theConsignmentMessageStr);
                if (isset($consignmentIntRetriever)) {
                    return $consignmentIntRetriever;
                }else {
                    return null;
                }
            }else {
                return null;
            }
        }else{
            return null;
        }
    }
}