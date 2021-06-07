<?php

namespace Aalogics\Lcs\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use \Aalogics\Lcs\Logger\Logger;

class Data extends AbstractHelper
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Aalogics\Lcs\Logger\Logger
     */
    protected $_log;
    
    protected  $_resource;

    /**
     * Data constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
    	\Aalogics\Lcs\Logger\Logger $logger,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
    	$this->_log = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->_resource = $resource;
        parent::__construct($context);
    }

    public function getStoreName() {
    	return $this->scopeConfig->getValue(
        'general/store_information/name',
        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
    );
    }
    
    public function getShippingOriginCity() {
    	return $this->scopeConfig->getValue(
    			'shipping/origin/city',
    			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
    	);
    }

    public function isEnabled() {
        return $this->getAdminField('lcs_cod/enable');
    }

    public function isCitiesEnabled() {
        return $this->getAdminField('aacities/enable');
    }

    public function isShippingEnabled() {
    	return $this->getAdminField('lcs_inv_shipp_action/enable');
    }
    
    public function isNonCodEnabled() {
    	return $this->getAdminField('lcs_non_cod/enable');
    }
    
    public function getTrackingUrl($tracking) {
    	$externalUrl =  $this->getAdminField('lcs_cod/tracking_url');
    	return $externalUrl . $tracking;
    }
    /**
     *
     */
    public function getAdminField( $key ) {
    	$value = $this->scopeConfig->getValue('aalcs/'.$key, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    	return $value;
    }
    
    public function debug($message, $data = NULL) {
    	if($this->getAdminField('lcs_cod/debug')) {
    		$this->_log->debug($message.print_r($data,TRUE));
    	}
    }

    public function getLcsCityData($cityName){
        $adapter = $this->_resource->getConnection();
        $result = $adapter->fetchAll("SELECT * FROM `pakistan_cities_lcs` WHERE `default_name` = '".$cityName."'");
        if(!empty($result))
            return $result[0]['city_id'];
        else
            return '';
    }

    public function cleanPhoneNumber($phone){
        return preg_replace('/\D+/', '', $phone);
    }

}
