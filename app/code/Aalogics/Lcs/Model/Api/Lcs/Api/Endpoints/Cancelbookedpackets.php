<?php

namespace Aalogics\Lcs\Model\Api\Lcs\Api\Endpoints;

use Aalogics\Lcs\Model\Api\Lcs\Api\EndpointInterface;
use \Magento\Framework\DataObject;
use \Aalogics\Lcs\Helper\Data;

class Noncod extends DataObject implements EndpointInterface {
	protected $_endpoint = 'cancelBookedPackets';
	protected $_lcsHelper;
	protected $scopeConfigObject;
	
	/**
	 *
	 * @var \Magento\Framework\Json\Helper\Data
	 */
	protected $jsonHelper;
	public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Aalogics\Lcs\Helper\Data $lcsHelper, array $data = []) {
		$this->_lcsHelper = $lcsHelper;
		$this->scopeConfigObject = $scopeConfig;
		$data ['wsdl'] = $this->_lcsHelper->getAdminField ( 'lcs_non_cod/api_url' );
		$data ['endpoint'] = $this->_endpoint;
		parent::__construct ( $data );
	}
	
	public function makeRequestParams($parameters = []) {
		$params = array (
				'api_key' => 'your_api_key',
				'api_password' => 'your_api_password',
				'cn_numbers' => 'string'  // E.g. 'XXYYYYYYYY' OR 'XXYYYYYYYY,XXYYYYYYYY,XXYYYYYY' 10 Digits each number
				);
		
		// remove empty array keys
		foreach ( $params as $key => $param ) {
			if (! $param && strlen ( $param ) == 0) {
				unset ( $params [$key] );
			}
		}
		return $params;
	}
	
	public function makeRequestHeaders($parameters = []) {
		return [ 
				'Authorization-Token' => $parameters ['access_token'],
				'Accept' => '*/*',
				'Accept-Encoding' => 'gzip, deflate',
				'Source-Identifier' => $parameters ['source_identifier'],
				'User-Agent' => 'runscope/0.1',
				'Content-Type' => 'application/json' 
		];
	}
}