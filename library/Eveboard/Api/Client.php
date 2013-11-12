<?php
namespace Eveboard\Api;

use Eveboard\Api\Exceptions\RequestError;
use Eveboard\Api\Exceptions\ApiError;
use SimpleXMLElement;

/**
 * Eve-Online API's client
 * 
 * @property \Eveboard\Api\Functions\Account $account Account information
 * @property \Eveboard\Api\Functions\Char    $char    Character information
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
	 * @throws ApiError
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
		
		if (! empty($params)) {
			$getParams += $params;
		}
		
		if (! empty($getParams)) {
			$url .= '?' . http_build_query($getParams);
		}
		
		$curl = curl_init($url);
		
		curl_setopt($curl, CURLOPT_USERAGENT, 'Eveboard');
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		
		$response = curl_exec($curl);
		$errorNm  = curl_errno($curl);
		$errorStr = curl_error($curl);
		
		curl_close($curl);
		
		if ($response === false) {
			throw new RequestError("Unable to perform the request: $errorStr; Url: $url", $errorNm);
		}
		
		$document = new SimpleXMLElement($response);
		
		if (isset($document->error)) {
			$errAttrs = $document->error->attributes();
			$errorNm  = isset($errAttrs->code) ? (int) $errAttrs->code : 0;
			
			throw new ApiError("Invalid request: $document->error; Url: $url", $errorNm);
		}
		
		if (! isset ($document->result)) {
			throw new RequestError("Have no result; Url: $url");
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