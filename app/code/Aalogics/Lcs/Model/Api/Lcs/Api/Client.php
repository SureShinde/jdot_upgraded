<?php
namespace Aalogics\Lcs\Model\Api\Lcs\Api;

use \Magento\Framework\HTTP\ZendClientFactory;
use \Aalogics\Lcs\Model\Api\Lcs\Api\Endpoints\Connect;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Aalogics\Lcs\Logger\Logger;
use \Magento\Framework\HTTP\ZendClient;
use \Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use \Magento\Framework\Xml\Parser;
use \Aalogics\Lcs\Helper\Data as LcsHelper;
use \Magento\Framework\Webapi\Soap\ClientFactory;

class Client extends \Magento\Framework\DataObject {
	
	/**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfigObject;

    /**
     * 
     * @var \Magento\Framework\App\Config\ConfigResource\ConfigInterface
     */
    protected $scopeConfigWriter;

    /**
     * @var \Aalogics\Lcs\Logger\Logger
     */
    protected $logger;

    /**
     * @var \Magento\Framework\HTTP\ZendClientFactory $clientFactory
     */
    protected $clientFactory;
    
    /**
     * 
     * @var \Aalogics\Lcs\Model\Api\EndpointFactory
     */
    protected $endpointFactory;
    
     /**
      * @var \Aalogics\Lcs\Helper\Data
      */
     protected $_helper;

     /**
      * @var \Aalogics\Lcs\Helper\Data
      */
     protected $curlClient;  

    /**
     * 
     * @var \Magento\Framework\Xml\Parser
     */
    protected $xmlHelper;

     /**
     * 
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;
	
	public function __construct(
			\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
			\Aalogics\Lcs\Logger\Logger $logger,
			\Magento\Framework\HTTP\ZendClientFactory $clientFactory,
			EndpointFactory $endpointFactory,
			ConfigInterface $scopeWriter,
			\Magento\Framework\HTTP\Client\Curl $curl,
			\Aalogics\Lcs\Helper\Data $LcsHelper,
			\Magento\Framework\Xml\Parser $xmlHelper,
			\Magento\Framework\Json\Helper\Data $jsonHelper,
			array $data = []
	) {
			$this->xmlHelper = $xmlHelper;
			$this->_helper = $LcsHelper;
			$this->scopeConfigWriter = $scopeWriter;
			$this->scopeConfigObject = $scopeConfig;
			$this->logger = $logger;
			$this->curlClient = $curl;
			$this->clientFactory = $clientFactory;		
			$this->endpointFactory = $endpointFactory;
			$this->jsonHelper = $jsonHelper;
			parent::__construct($data);
	}
	
	public function connect($username = null, $password = null, $sandbox = false, $forceCheck = false) {
		return $this;
	}
	
	public function makeRequest($endPoint , $method ,$params , $headers = array()) {

		$url = $this->_helper->getAdminField('lcs_cod/api_url');
		$endPointObj = $this->endpointFactory->create($endPoint);
		$format = $this->_helper->getAdminField('lcs_cod/api_format');//XML/JSON

		if($this->_helper->getAdminField('lcs_cod/sandbox')) {
			$url = $url.$endPointObj->getData('endpoint').'Test';
		}else {
			$url = $url.$endPointObj->getData('endpoint');
		}
		
		//setting response format
		$url .= '/format/'.$format;

		$headers = array();
		
		/** @var \Magento\Framework\HTTP\ZendClient $client */
		$client = $this->clientFactory->create();
		$parameters = $endPointObj->makeRequestParams($params);

		$client->setRawData($this->jsonHelper->jsonEncode($parameters), 'application/json');
		
		$this->_helper->debug('Request URL',$url);
		$this->_helper->debug('Request Headers',$headers);
		$this->_helper->debug('Request Method',$method);
		$this->_helper->debug('Request Params',$parameters);
		
		$client->setUri($url);
		$client->setMethod($method);
		$client->setHeaders($headers);
		try {
			$response = $client->request();
		} catch (\Exception $e) {
			$this->_helper->debug('Exception : ',$e->getMessage());
			throw new \Exception ( 'Exception : '.$e->getMessage() );
		}
		if (($response->getStatus() < 200 || $response->getStatus() > 210)) {
			$this->_helper->debug('Deployment marker request did not send a 200 status code.');
			throw new \Exception ( 'Deployment marker request did not send a 200 status code' );
		}
		$response = $this->jsonHelper->jsonDecode($response->getBody());
		
		if(isset($response['status']) && $response['status']) {
			return $response;
		}else {
			$this->_helper->debug('Client Response Error:',$response);
			return $response;
		}
		
	}
}
