<?php
namespace Aalogics\Lcs\Model\Api\Lcs\Api;
use \Magento\Framework\Model\AbstractModel;
use \Aalogics\Lcs\Model\Api\Lcs\Api\Client;
use \Aalogics\Lcs\Helper\Data;
use \Magento\Framework\DataObject;
use \Magento\Framework\HTTP\ZendClient;

class Request extends DataObject {
	
	/**
	 * HTTP Client
	 * @var client
	 */
	protected $client;
	
	/**
	 * Helper
	 * @var \Aalogics\Lcs\Helper\Data
	 */
	protected $helper;
	
	/**
	 * 
	 * @param \Aalogics\Lcs\Model\Api\Lcs\Api\Client $client
	 * @param \Aalogics\Lcs\Logger\Logger $logger
	 */
	public function __construct(
		\Aalogics\Lcs\Model\Api\Lcs\Api\Client $client,
		\Aalogics\Lcs\Helper\Data $helper
	) {
		$this->client = $client;
		$this->helper = $helper;
	}	
	
	/**
	 * 
	 * @return boolean|\Magento\Customer\Model\Data\Customer
	 */
	public function bookCodOrder($parameters) {
		if (!$trackingId = $this->_bookCodOrder ($parameters)) {
			return false;
		}
	
		return $trackingId;
	}
	

	/**
	 *
	 * @return boolean|\Magento\Customer\Model\Data\Customer
	 */
	public function bookNonCodOrder($parameters) {
		if (!$products = $this->_bookNonCodOrder ($parameters)) {
			return false;
		}
	
		return $products;
	}
	
	/**
	 *
	 * @return boolean|\Magento\Customer\Model\Data\Customer
	 */
	public function getCities() {
		if (!$products = $this->_fetchCities ()) {
			return false;
		}
	
		return $products;
	}
	
	/**
	 * Connect to Lcs and sync products.
	 *
	 * @throws Exception
	 */
	protected function _bookCodOrder($parameters) {
		try {
			if ($this->client->connect ()) {
				$this->helper->debug('_bookCodOrder start');
				//check if customer exists , if exists send update request else create request
				$response = $this->client->makeRequest(
						\Aalogics\Lcs\Model\Api\Lcs\Api\Endpoints\Bookpacket::class,
						ZendClient::POST,
						$this->_makeRequestArray('cod_order',$parameters)
				);
			} else {
				throw new \Exception ( 'Unable to connect to LCS' );
			}
			return $response;
		} catch ( \Exception $e ) {
			throw $e;
		}
	
	}
	
	/**
	 * Connect to Lcs and sync cities.
	 *
	 * @throws Exception
	 */
	protected function _fetchCities() {
		try {

			if ($this->client->connect ()) {
				
				$this->helper->debug('_fetchCities start');
				
				$response = $this->client->makeRequest(
						\Aalogics\Lcs\Model\Api\Lcs\Api\Endpoints\Cities::class,
						ZendClient::POST,
						[]
				);

				if(count($response) > 0) {
					return $response;
				}else {
					$this->helper->debug('Response',$response);
					throw new \Exception ( 'Unable to fetch cities' );
				}
			} else {
				throw new \Exception ( 'Unable to connect to LCS' );
			}
		} catch ( \Exception $e ) {
			throw $e;
		}
	
	}
	
	/**
	 * Connect to Lcs and sync products.
	 *
	 * @throws Exception
	 */
	protected function _bookNonCodOrder($parameters) {
		try {
			if ($this->client->connect ()) {
				$this->helper->debug('_bookCodOrder start');
				//check if customer exists , if exists send update request else create request
				$response = $this->client->makeRequest(
						\Aalogics\Lcs\Model\Api\Lcs\Api\Endpoints\Noncod::class,
						$this->_makeRequestArray('non_cod_order',$parameters)
				);
				if($response['corpLcs']['response'] == 'OK') {
					return TRUE;
				}else {
					$this->helper->debug('Response',$response);
					throw new \Exception ( 'Unable to deliver message' );
				}
			} else {
				throw new \Exception ( 'Unable to connect to Telenor' );
			}
		} catch ( \Exception $e ) {
			throw $e;
		}
	
	}
	
	
	/**
	 * 
	 * @param unknown $type
	 * @param unknown $parameters
	 * @return multitype:NULL string unknown Ambigous <string, unknown>
	 */
	protected function _makeRequestArray($type, $parameters = []) {
		$result = [];
		
		switch ($type) {
			case 'cod_order':
				$result = $parameters;
				break;
			case 'non_cod_order':
				$result = $parameters;
				break;
		}
		return $result;
	}
}