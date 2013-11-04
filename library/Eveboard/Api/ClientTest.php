<?php
namespace Eveboard\Api;

use SimpleXMLElement;

class ClientTest extends Client {
	
	protected $_fakeResponse;
	
	public function setResponseXml($xml) {
		$this->_fakeResponse = new SimpleXMLElement($xml);
	}
	
	public function request($section, $function, array $params = null) {
		return $this->_fakeResponse->result;
	}
	
}