<?php


namespace Drupal\singpost_toolbox\Helper;


use DOMDocument;
use Drupal;
use Drupal\Component\Serialization\Json;
use Drupal\singpost_audit_trail\Model\AuditTrail;
use Drupal\singpost_toolbox\Form\Config\ToolboxConfigForm;

/**
 * Class ToolboxBase
 *
 * @package Drupal\singpost_toolbox
 */
class ToolboxBase{

	public static $config_name;

	public static $authorize_header_name;

	public static $authorize_header_key;

	protected $_config;

	protected $_authorize_header_name;

	protected $_authorize_header_key;

	/**
	 * ToolboxBase constructor.
	 */
	public function __construct(){
		$this->_config = Drupal::config(static::$config_name);
		$this->_authorize_header_name = static::$authorize_header_name;
		$this->_authorize_header_key  = static::$authorize_header_key;
	}

	/**
	 * @param $esb_url
	 * @param string $format
	 * @param string $request
	 * @param string $action
	 * @param string $method
	 *
	 * @return mixed|\SimpleXMLElement|string
	 */
	protected function _getEsbResult(
		$esb_url,
		$format = 'xml',
		$request = '',
		$action = '',
		$method = 'POST',
		$log = TRUE){
		$requested_at = Drupal::time()->getCurrentMicroTime();

		$result = '';

		$esb_timeout = Drupal::service('settings')->get('esb_timeout') ?? 30;

		if ($method == 'POST'){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $esb_url);
			curl_setopt($ch, CURLOPT_HTTPHEADER,
				[
					"{$this->authKey()}",
					"Cache-Control: no-cache",
					"Content-Type: application/{$format}",
				]);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_TIMEOUT, $esb_timeout);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_REFERER, $esb_url);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_VERBOSE, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
			curl_setopt($ch, CURLOPT_POST, 1);
		}else{
			$ch = curl_init($esb_url);
			curl_setopt($ch, CURLOPT_HTTPHEADER, [
				"Authorization: {$this->_authToken('locate_us')}",
			]);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		}

		$esb_result = curl_exec($ch);
		curl_close($ch);

		$status = AuditTrail::TYPE_FAILED;

		if (!empty($esb_result)){
			$status = AuditTrail::TYPE_SUCCESS;
			if ($format == 'xml'){
				if (strpos($esb_result, 'Rate limit exceeded') !== FALSE){
					$result = ['error' => 'Rate limit exceeded'];
				}else{
					$result = @simplexml_load_string($esb_result);
					$result = Json::decode(Json::encode($result));
				}
			}else{
				$result = Json::decode($esb_result);
			}
		}

		// audit api result
		if (empty($action)){
			$action = explode("\\", static::class);
			$action = end($action);
		}

		$responded_at = Drupal::time()->getCurrentMicroTime();

		if ($log){
			Drupal::service('singpost.audit_trail.service')
			      ->log($action, $request, $esb_result, $esb_url, $requested_at,
				      $responded_at, $status);
		}

		return $result;
	}

	/**
	 * @param string $root
	 * @param array $children
	 * @param bool $cdata
	 * @param string $xmlns
	 *
	 * @return string
	 */
	protected function _generateXmlInput(
		$root = '',
		$children = [],
		$cdata = TRUE,
		$xmlns = 'http://singpost.com/paw/ns'){
		$dom      = new DOMDocument();
		$xml_root = $dom->createElement($root);
		$xml_root->setAttribute('xmlns', $xmlns);

		foreach ($children as $elem => $child){
			$child_elem = $dom->createElement($elem);
			if (is_array($child)){
				foreach ($child as $child_value){
					foreach ($child_value as $cv_elem => $cv_valure){
						if ($cdata){
							$cdata_child_value = $dom->createCDATASection($cv_valure);
							$child_child_elem  = $dom->createElement($cv_elem);
							$child_child_elem->appendChild($cdata_child_value);
						}else{
							$child_child_elem = $dom->createElement($cv_elem, $cv_valure);
						}
						$child_elem->appendChild($child_child_elem);
					}
				}
			}elseif ($cdata){
				$cdata_value = $dom->createCDATASection($child);
				$child_elem->appendChild($cdata_value);
			}else{
				$child_elem = $dom->createElement($elem, $child);
			}

			$xml_root->appendChild($child_elem);
		}

		$dom->appendChild($xml_root);

		return $dom->saveXML($dom->documentElement);
	}

	/**
	 * @param null $type
	 *
	 * @return array|mixed|null
	 */
	private function _authToken($type = NULL){
		$config = Drupal::config(ToolboxConfigForm::$config_name);

		if (isset($type)){
			return $config->get('toolbox_auth_token_locate_us');
		}else{
			return $config->get('toolbox_auth_token');
		}
	}

	/**
	 * @return string
	 */
	public function authKey(){
		$header_name = !empty($this->_authorize_header_name) ? $this->_config->get($this->_authorize_header_name) : 'Authorization';
		$header_key  = !empty($this->_authorize_header_key) ? $this->_config->get($this->_authorize_header_key) : "{$this->_authToken()}";

		return "$header_name: $header_key";
	}
}