<?php

namespace Aalogics\Lcs\Model\Api\Lcs\Api\Endpoints;

use Aalogics\Lcs\Model\Api\Lcs\Api\EndpointInterface;
use \Aalogics\Lcs\Helper\Data;
use \Magento\Framework\DataObject;

class Bookpacket extends DataObject implements EndpointInterface
{
    protected $_endpoint = 'bookPacket';
    protected $_lcsHelper;
    protected $scopeConfigObject;

    /**
     *
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;
    public function __construct(
    	\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, 
    	\Aalogics\Lcs\Helper\Data $lcsHelper, 
    	array $data = []
    )
    {
        $this->_lcsHelper        = $lcsHelper;
        $this->scopeConfigObject = $scopeConfig;
        $data['wsdl']            = $this->_lcsHelper->getAdminField('lcs_cod/api_url');
        $data['endpoint']        = $this->_endpoint;
        parent::__construct($data);
    }
    public function makeRequestParams($parameters = [])
    {
    	$originCity = $this->_lcsHelper->getAdminField('lcs_setting_backend/city_name');
    	$origin_city = $this->_lcsHelper->getLcsCityData($originCity);
    	
    	$destination_city = $parameters['destination_city_name'];

    	$shipmentPhone = $this->_lcsHelper->getAdminField('lcs_setting_backend/shipment_phone');
    	$shipment_phone = $this->_lcsHelper->cleanPhoneNumber($shipmentPhone);
    	$consignment_phone = $this->_lcsHelper->cleanPhoneNumber($parameters['telephone']);

    	$params = array(
            'api_key'                      => $this->_lcsHelper->getAdminField('lcs_cod/api_key'),
            'api_password'                 => $this->_lcsHelper->getAdminField('lcs_cod/api_password'),
            'booked_packet_weight'         => $parameters['weight']*1000, // Weight should in 'Grams' e.g. '2000'
            // 'booked_packet_vol_weight_w'   => int, // Optional Field (You can keep it empty), Volumetric Weight Width
            // 'booked_packet_vol_weight_h'   => int, // Optional Field (You can keep it empty), Volumetric Weight Height
            // 'booked_packet_vol_weight_l'   => int, // Optional Field (You can keep it empty), Volumetric Weight Length

            'booked_packet_order_id'       => $parameters['order_id'], // Optional Filed, (If any) Order ID of Given Product
            'booked_packet_no_piece'       => $parameters['qty'], // No. of Pieces should an Integer Value
            'booked_packet_collect_amount' => $parameters['grant_total'], // Collection Amount on Delivery

            'origin_city'                  => $origin_city, // Params: 'self' or 'integer_value' e.g. 'origin_city' => 'self' or 'origin_city' => 789 (where 789 is Lahore ID)
            // If 'self' is used then Your City ID will be used.
            // 'integer_value' provide integer value (for integer values read 'Get All Cities' api documentation)
            'destination_city'             => $destination_city, // Params: 'self' or 'integer_value' e.g. 'destination_city' => 'self' or 'destination_city' => 789 (where 789 is Lahore ID)
            // If 'self' is used then Your City ID will be used.
            // 'integer_value' provide integer value (for integer values read 'Get All Cities' api documentation)

            'shipment_name_eng'            => $this->_lcsHelper->getAdminField('lcs_setting_backend/shipment_name'), // Params: 'self' or 'Type any other Name here', If 'self' will used then Your Company's Name will be Used here
            'shipment_email'               => $this->_lcsHelper->getAdminField('lcs_setting_backend/shipment_email'), // Params: 'self' or 'Type any other Email here', If 'self' will used then Your Company's Email will be Used here
            'shipment_phone'               => $shipment_phone, // Params: 'self' or 'Type any other Phone Number here', If 'self' will used then Your Company's Phone Number will be Used here
            'shipment_address'             => $this->_lcsHelper->getAdminField('lcs_setting_backend/shipment_address'), // Params: 'self' or 'Type any other Address here', If 'self' will used then Your Company's Address will be Used here
            'consignment_name_eng'         => $parameters['consignee_name'], // Type Consignee Name here
            'consignment_email'            => $parameters['consignee_email'], // Optional Field (You can keep it empty), Type Consignee Email here
            'consignment_phone'            => $consignment_phone, // Type Consignee Phone Number here
            // 'consignment_phone_two'        => 'int', // Optional Field (You can keep it empty), Type Consignee Second Phone Number here
            // 'consignment_phone_three'      => 'int', // Optional Field (You can keep it empty), Type Consignee Third Phone Number here
            'consignment_address'          => $parameters['consignee_address'], // Type Consignee Address here
            'special_instructions'         => $this->_lcsHelper->getAdminField('lcs_setting_backend/special_instructions'), // Type any instruction here regarding booked packet
            // 'shipment_type'                => 'string', // Optional Field (You can keep it empty so It will pick default value i.e. "overnight"), Type Shipment type name here
        );

        // remove empty array keys
        foreach ($params as $key => $param) {
            if (!$param && strlen($param) == 0) {
                unset($params[$key]);
            }
        }
        return $params;
    }
    public function makeRequestHeaders($parameters = [])
    {
        return [
            'Authorization-Token' => $parameters['access_token'],
            'Accept'              => '*/*',
            'Accept-Encoding'     => 'gzip, deflate',
            'Source-Identifier'   => $parameters['source_identifier'],
            'User-Agent'          => 'runscope/0.1',
            'Content-Type'        => 'application/json',
        ];
    }
    public function parseOutput($response)
    {
        $responseProperty = $this->getEndpoint() . 'Result';
        $responseString   = $response->{$responseProperty};
        if (strpos('invalid', strtolower($responseString)) !== false) {
            throw new \Exception('Exception : ' . $responseString);
        }
        return $responseString;
    }
}
