<?php
namespace Aalogics\Lcs\Model\Api\Lcs\Api\Endpoints;
use Aalogics\Lcs\Model\Api\Lcs\Api\EndpointInterface;
use \Magento\Framework\DataObject;
use \Aalogics\Lcs\Logger\Logger;
use \Magento\Framework\App\Config\ScopeConfigInterface;

class Connect extends DataObject implements EndpointInterface {

	protected $_endpoint = 'api/auth.jsp';
	
	protected $logger;
	
	protected $scopeConfigObject;
	
	public function __construct(
			\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
			\Aalogics\Lcs\Logger\Logger $logger,
			array $data = []
	) {
		$this->_tcsHelper = $tcsHelper;
		$this->scopeConfigObject = $scopeConfig;
		$data['endpoint'] = $this->_endpoint;
		parent::__construct($data);
	}
	
	public function makeRequestParams($parameters = []) {
		//use refresh token if exists
			$params = array(
			);
		$this->logger->debug('Oaurth Params'.print_r($params, TRUE));
		return $params;
	}
	
	public function makeRequestHeaders($parameters = []) {
		$headers = array(
				'Content-Type' => 'application/x-www-form-urlencoded',
				'Accept' =>  '*/*',
				'Accept-Encoding' => 'gzip, deflate',
				'Content-Length' => '40',
				'Source-Identifier' => $parameters['source_identifier'],
				'User-Agent' => 'runscope/0.1'
		);
		$this->logger->debug('Oauth Headers'.print_r($headers, TRUE));
		return $headers;
		
	}
	
	public function checkToken() {
		$this->_endpoint = 'connect/introspect';
	}
	
}