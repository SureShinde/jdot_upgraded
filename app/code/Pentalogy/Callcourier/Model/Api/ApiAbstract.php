<?php
namespace Pentalogy\Callcourier\Model\Api;

use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Store\Model\ScopeInterface;
abstract class ApiAbstract{
    const API_URL = "http://cod.callcourier.com.pk/API/CallCourier/";
    const CALL_COURIER_LOG_FILE_NAME = "call_courier.log";
    const SEL_ORIGIN = 'Domestic';
    const MY_BOX_ID = 3;
    const HOLIDAY = 'false';
    const SPECIAL_HANDLING = 'false';
    const XML_API_KEY = "callcouriertabsection/callcouriergroup/apikey";
    const XML_SHIPPER_NAME = "callcouriertabsection/callcouriergroup/shippername";
    const XML_SHIPPER_CITY = "callcouriertabsection/callcouriergroup/shippercity";
    const XML_SHIPPER_ORIGIN = "callcouriertabsection/callcouriergroup/shipperorigin";
    const XML_SHIPPER_ADDRESS = "callcouriertabsection/callcouriergroup/shipperaddress";
    const XML_SHIPPER_EMAIL = "callcouriertabsection/callcouriergroup/shipperemail";
    const XML_SHIPPER_CELL_NO = "callcouriertabsection/callcouriergroup/shippercellno";
    const XML_SHIPPER_LANDING_NO = "callcouriertabsection/callcouriergroup/shipperlandlineno";
    const XML_SHIPPER_REMARKS = "callcouriertabsection/callcourier_bulk_shipment/remarks";
    const XML_SERVICE_TYPE = "callcouriertabsection/callcourier_bulk_shipment/service_type";
    const XML_BEFORE_ORDER_STATUS = "callcouriertabsection/callcourier_bulk_shipment/before_booking_order_status";
    protected $_scopeConfig;
    protected $systemConfiguration;
    protected $objectManager;
    private $logger;
    public function __construct() {
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_callCouriersSystemConfiguration();
        $this->logger = $this->objectManager->get('\Psr\Log\LoggerInterface');
    }

    protected function _doRequest($method, $data = null)
    {
        $url = self::API_URL . $method;
        $queryStr = null;
        if ($data != null) {
            $queryStr = http_build_query($data);
        }
        $url = ($queryStr != null) ? $url . "?" . $queryStr : $url;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $curlResponse = curl_exec($ch);
        if (curl_error($ch)) {
            $this->logger->critical('Call Courier error message' . curl_error($ch));
        }
        curl_close($ch);
        return $curlResponse;
    }

    protected function _callCouriersSystemConfiguration()
    {
        $this->systemConfiguration['loginId'] = $this->_getSystemConfigValue(self::XML_API_KEY);
        $this->systemConfiguration['Origin'] = $this->_getSystemConfigValue(self::XML_SHIPPER_CITY);
        $this->systemConfiguration['ShipperName'] = $this->_getSystemConfigValue(self::XML_SHIPPER_NAME);
        $this->systemConfiguration['ShipperArea'] = $this->_getSystemConfigValue(self::XML_SHIPPER_ORIGIN);;
        $this->systemConfiguration['ShipperCity'] = $this->_getSystemConfigValue(self::XML_SHIPPER_CITY);
        $this->systemConfiguration['ShipperLandLineNo'] = $this->_getSystemConfigValue(self::XML_SHIPPER_LANDING_NO);
        $this->systemConfiguration['ShipperCellNo'] = $this->_getSystemConfigValue(self::XML_SHIPPER_CELL_NO);
        $this->systemConfiguration['ShipperEmail'] = $this->_getSystemConfigValue(self::XML_SHIPPER_EMAIL);
        $this->systemConfiguration['ShipperAddress'] = $this->_getSystemConfigValue(self::XML_SHIPPER_ADDRESS);
        $this->systemConfiguration['SelOrigin'] = self::SEL_ORIGIN;
        $this->systemConfiguration['SpecialHandling'] = self::SPECIAL_HANDLING;
        $this->systemConfiguration['MyBoxId'] = self::MY_BOX_ID;
        $this->systemConfiguration['Holiday'] = self::HOLIDAY;
    }

    protected function _getSystemConfigValue($xmlPath){
        return trim($this->objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')
            ->getValue($xmlPath));
    }
}
