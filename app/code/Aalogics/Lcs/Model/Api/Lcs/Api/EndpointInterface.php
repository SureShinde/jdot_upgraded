<?php
namespace Aalogics\Lcs\Model\Api\Lcs\Api;

interface EndpointInterface {
	
	public function makeRequestParams($parameters = []);
	
	public function makeRequestHeaders($parameters = []);
}