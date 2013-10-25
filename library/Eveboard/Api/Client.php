<?php
namespace Eveboard\Api;

use Eveboard\Api\Exceptions\RequestError;
use SimpleXMLElement;

/**
 * Eve-Online API's client
 * 
 * @property \Eveboard\Api\Functions\Account $account Account information
 * @property \Eveboard\Api\Functions\Corp    $corp    Corporation information
 */
class Client {
	
	const SECTIONS_NAMESPACE = 'Eveboard\Api\Functions';
	
	const API_TEMPLATE =  'https://api.eveonline.com/%s/%s.xml.aspx';
	
	const PARAM_KEY_ID   = 'keyID';
	const PARAM_KEY_CODE = 'vCode';
	
	/**
	 * Key ID / User ID
	 *
	 * @var int
	 */
	protected $_keyId;
	
	/**
	 * Verification code / Api key
	 *
	 * @var string
	 */
	protected $_keyCode;
	
	/**
	 * Set the Key ID / User ID
	 * 
	 * @param  int $id
	 * @return Client
	 */
	public function setKeyId($id) {
		$this->_keyId = $id;
		
		return $this;
	}
	
	/**
	 * Set the Verification code / Api key
	 * 
	 * @param  string $code
	 * @return Client
	 */
	public function setKeyCode($code) {
		$this->_keyCode = $code;
		
		return $this;
	}
	
	/**
	 * Execute the request to the Eve-Online API
	 * 
	 * @param  string $section
	 * @param  string $function
	 * @param  array  $params
	 * @return SimpleXMLElement
	 * @throws RequestError
	 */
	public function request($section, $function, array $params = null) {
		$url = sprintf(self::API_TEMPLATE, strtolower($section), ucfirst($function));
		
		$getParams = [];
		
		if ($this->_keyId !== null) {
			$getParams[self::PARAM_KEY_ID] = $this->_keyId;
		}
		
		if ($this->_keyCode !== null) {
			$getParams[self::PARAM_KEY_CODE] = $this->_keyCode;
		}
		
		if ($params !== null) {
			$getParams += $params;
		}
		
		if (! empty($getParams)) {
			$url .= '?' . http_build_query($getParams);
		}
		
		$errorMsg;
		
		set_error_handler(function() use(&$errorMsg) {
			$args = func_get_args();
			
			if (isset($args[4]['http_response_header'][0])) {
				$errorMsg = $args[4]['http_response_header'][0];
			}
		});
		
		$response = file_get_contents($url);
		
		restore_error_handler();
		
		if ($response === false || $errorMsg !== null) {
			throw new RequestError('Unable to perform request: ' . $errorMsg);
		}
		
		$document = new SimpleXMLElement($response);
		
		if (! isset ($document->result)) {
			throw new RequestError('Have no result');
		}
		
		return $document->result;
	}
	
	/**
	 * Get the section
	 * 
	 * @param string $name
	 */
	public function __get($name) {
		$class = self::SECTIONS_NAMESPACE . '\\' . ucfirst($name);
		
		return new $class($this);
	}
	
}