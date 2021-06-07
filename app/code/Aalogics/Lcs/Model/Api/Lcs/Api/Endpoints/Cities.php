<?php
namespace Aalogics\Lcs\Model\Api\Lcs\Api\Endpoints;
use Aalogics\Lcs\Model\Api\Lcs\Api\EndpointInterface;
use \Magento\Framework\DataObject;
use \Aalogics\Lcs\Helper\Data;
use \Magento\Framework\Xml\Parser;

class Cities extends DataObject implements EndpointInterface {
	protected $_endpoint = 'getAllCities';
	
	protected $_lcsHelper;
	
	protected $scopeConfigObject;
	
	 /**
     * 
     * @var \Magento\Framework\Xml\Parser
     */
    protected $xmlHelper;
	
	public function __construct(
			\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
			\Aalogics\Lcs\Helper\Data $lcsHelper,
			\Magento\Framework\Xml\Parser $xmlHelper,
			array $data = []
	) {
		$this->_lcsHelper = $lcsHelper;
		$this->xmlHelper = $xmlHelper;
		$this->scopeConfigObject = $scopeConfig;
		$data['endpoint'] = $this->_endpoint;
		parent::__construct($data);
	}
	
	public function makeRequestParams($parameters = []) {
		
		$params = array(
			'api_key' => $this->_lcsHelper->getAdminField('lcs_cod/api_key'),
			'api_password' => $this->_lcsHelper->getAdminField('lcs_cod/api_password')
		);

		//remove empty array keys
		foreach ($params as $key => $param) {
			if(!$param && strlen($param) == 0 ) {
				unset($params[$key]);
			}
		}
		return $params;
	}
	
	public function makeRequestHeaders($parameters = []) {
		return [
				'Authorization-Token' => $parameters['access_token'],
				'Accept' => '*/*',
				'Accept-Encoding' => 'gzip, deflate',
				'Source-Identifier' => $parameters['source_identifier'],
				'User-Agent' => 'runscope/0.1',
				'Content-Type' => 'application/json'
		];
		
	}
	
	public function parseOutput($xml) {
		$responseProperty = $this->getEndpoint().'Result';
		$string = '<xs:schema xmlns="" xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:msdata="urn:schemas-microsoft-com:xml-msdata" id="NewDataSet"><xs:element name="NewDataSet" msdata:IsDataSet="true" msdata:MainDataTable="Table" msdata:UseCurrentLocale="true"><xs:complexType><xs:choice minOccurs="0" maxOccurs="unbounded"><xs:element name="Table"><xs:complexType><xs:sequence><xs:element name="CityID" type="xs:int" minOccurs="0"/><xs:element name="CityName" type="xs:string" minOccurs="0"/><xs:element name="CityCode" type="xs:string" minOccurs="0"/><xs:element name="AREA" type="xs:string" minOccurs="0"/></xs:sequence></xs:complexType></xs:element></xs:choice></xs:complexType></xs:element></xs:schema>';
		$response = str_replace($string, '', $xml->{$responseProperty}->any);
		$response = $this->xmlHelper->loadXML($response)->xmlToArray();
		return $response['diffgr:diffgram']['NewDataSet']['Table'];
		
	}
	
}