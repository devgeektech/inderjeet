<?php


namespace Drupal\singpost_toolbox_find_postal_code\Helper;


use Drupal\singpost_toolbox\Helper\ToolboxBase;

/**
 * Class FindPostalCode
 *
 * @package Drupal\singpost_toolbox_find_postal_code\Helper
 */
class FindPostalCode extends ToolboxBase{

	const TYPE = ['P' => 'PO Box', 'B' => 'Locked Bag'];

	const BOX = 'P';

	const LOCKED = 'B';

	public static $config_name = 'find_postal_code.config';

	public static $authorize_header_name = 'fpc_authorize_header_name';

	public static $authorize_header_key = 'fpc_authorize_header_key';

	/**
	 * @param $building_no
	 * @param $street_name
	 *
	 * @return array|mixed|\SimpleXMLElement|string
	 */
	public function getByStreet($building_no, $street_name){
		$esb_url = $this->_config->get('fpc_street_api');

		$root_elem = 'PostalCodeByStreetDetailsRequest';

		$children = [
			'BuildingNo' => $building_no,
			'StreetName' => $street_name,
		];

		$input = $this->_generateXmlInput($root_elem, $children, FALSE);

		$result = $this->_getEsbResult($esb_url, 'xml', $input);

		if (!empty($result) && !empty($result['PostalCodeByStreetDetailList']['PostalCodeByStreetDetail']) && !empty($result['Status']['ErrorCode']) && $result['Status']['ErrorCode'] == '000000'){
			$result = $result['PostalCodeByStreetDetailList']['PostalCodeByStreetDetail'];
			if (!empty($result['BuildingNo'])){
				$result = [$result];
			}
		}else{
			$result = [];
		}

		return $result;
	}

	/**
	 * @param $building_name
	 *
	 * @return array|mixed|\SimpleXMLElement|string
	 */
	public function getByLandmark($building_name){
		$esb_url = $this->_config->get('fpc_landmark_api');

		$root_elem = 'PostalAddressByLandMarkDetailsRequest';

		$children = [
			'BuildingName' => $building_name
		];

		$input = $this->_generateXmlInput($root_elem, $children, FALSE);

		$result = $this->_getEsbResult($esb_url, 'xml', $input);

		if (!empty($result) && !empty($result['PostalAddressByLandMarkDetailList']['PostalAddressByLandMarkDetail']) && !empty($result['Status']['ErrorCode']) && $result['Status']['ErrorCode'] == '000000'){
			$result = $result['PostalAddressByLandMarkDetailList']['PostalAddressByLandMarkDetail'];
			if (!empty($result['BuildingName'])){
				$result = [$result];
			}
		}else{
			$result = [];
		}

		return $result;

	}

	/**
	 * @param $type
	 * @param $delivery_no
	 * @param $post_office
	 *
	 * @return array|mixed|\SimpleXMLElement|string
	 */
	public function getByPOBox($type, $delivery_no, $post_office){
		$esb_url = $this->_config->get('fpc_pobox_api');

		$root_elem = 'PostalCodeByPOBoxDetailsRequest';

		$children = [
			'WindowDeliveryNo' => $delivery_no,
			'Type'             => $type,
			'PostOffice'       => $post_office
		];

		$input = $this->_generateXmlInput($root_elem, $children, FALSE);

		$result = $this->_getEsbResult($esb_url, 'xml', $input);

		if (!empty($result) && !empty($result['PostalCodeByPOBoxDetailList']['PostalCodeByPOBoxDetail']) && !empty($result['Status']['ErrorCode']) && $result['Status']['ErrorCode'] == '000000'){
			$result = $result['PostalCodeByPOBoxDetailList']['PostalCodeByPOBoxDetail'];
			if (!empty($result['Type'])){
				$result = [$result];
			}
		}else{
			$result = [];
		}

		return $result;
	}
}