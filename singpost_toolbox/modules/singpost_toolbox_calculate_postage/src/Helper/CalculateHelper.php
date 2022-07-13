<?php


namespace Drupal\singpost_toolbox_calculate_postage\Helper;


use Drupal;
use Drupal\Core\Locale\CountryManager;
use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Drupal\singpost_toolbox\Helper\ToolboxBase;
use Drupal\singpost_toolbox_calculate_postage\Model\DeliveryServiceModel;

/**
 * Class CalculateHelper
 *
 * @package Drupal\singpost_toolbox_calculate_postage\Helper
 */
class CalculateHelper extends ToolboxBase{

	public static $config_name = 'calculate.config';

	public static $authorize_header_name = 'cal_authorize_header_name';

	public static $authorize_header_key = 'cal_authorize_header_key';

	const WEIGHT_LIST = [
		2000  => 'Up to 2kg',
		5000  => 'Up to 5kg',
		30000 => 'Up to 30kg'
	];

	const WEIGHT_UNIT = [
		'g'  => 'Grams',
		'kg' => 'Kg'
	];

	const FROM_POSTAL_CODE = 408600;

	const TO_POSTAL_CODE = 408600;

	const DIMENSION_SIZE_PACKAGE = 'Package';

	protected $_dimension;

	protected $_delivery;

	/**
	 * CalculateHelper constructor.
	 */
	public function __construct(){
		parent::__construct();

		$this->_dimension = Drupal::service('singpost.toolbox.calculate.service.dimension');
		$this->_delivery  = Drupal::service('singpost.delivery_service.service');
	}

	/**
	 * @return array
	 */
	public function getListCountry(){
		return CountryManager::getStandardList();
	}

	/**
	 * @param $weight
	 * @param $unit
	 * @param $dimension
	 * @param $from_postal_code
	 * @param $to_postal_code
	 *
	 * @return array|mixed|\SimpleXMLElement|string
	 */
	public function calculateForSingapore(
		$weight,
		$unit,
		$dimension,
		$from_postal_code = self::FROM_POSTAL_CODE,
		$to_postal_code = self::TO_POSTAL_CODE){
		$esb_url = $this->_config->get('singapore_url');

		$root_elem = 'SingaporePostalInfoDetailsRequest';

		$weight = $this->getWeightByUnit($weight, $unit);

		$children = [
			'FromPostalCode'      => $from_postal_code,
			'ToPostalCode'        => $to_postal_code,
			'Weight'              => $weight,
			'DeliveryServiceName' => '',
			'Size'                => $dimension['size'],
			'Length'              => $dimension['length'],
			'Width'               => $dimension['width'],
			'Height'              => $dimension['height'],
		];

		$input = $this->_generateXmlInput($root_elem, $children);

		$result = $this->_getEsbResult($esb_url, 'xml', $input, 'CalculatePostage');

		if (!empty($result) && !empty($result['SingaporePostalInfoDetailList']['SingaporePostalInfoDetail']) && !empty($result['Status']['ErrorCode']) && $result['Status']['ErrorCode'] == '000000'){
			$result = $result['SingaporePostalInfoDetailList']['SingaporePostalInfoDetail'];

			if (!empty($result['DeliveryServiceName'])){
				$result = [$result];
			}
		}else{
			$result = [];
		}

		return $result;
	}

	/**
	 * @param $country
	 * @param $weight
	 * @param $unit
	 *
	 * @return array|mixed|\SimpleXMLElement|string
	 */
	public function calculateForOverSea(
		$country,
		$weight,
		$unit){
		$esb_url = $this->_config->get('oversea_url');

		$root_elem = 'OverseasPostalInfoDetailsRequest';

		$weight = $this->getWeightByUnit($weight, $unit);

		$children = [
			'Country'             => $country,
			'Weight'              => $weight,
			'DeliveryServiceName' => '',
			'ItemType'            => '',
			'PriceRange'          => '',
			'DeliveryTimeRange'   => '',
		];

		$input = $this->_generateXmlInput($root_elem, $children);

		$result = $this->_getEsbResult($esb_url, 'xml', $input, 'CalculatePostage');

		if (!empty($result) && !empty($result['OverseasPostalInfoDetailList']['OverseasPostalInfoDetail']) && !empty($result['Status']['ErrorCode']) && $result['Status']['ErrorCode'] == '000000'){
			$result = $result['OverseasPostalInfoDetailList']['OverseasPostalInfoDetail'];

			if (!empty($result['DeliveryServiceName'])){
				$result = [$result];
			}
		}else{
			$result = [];
		}

		return $result;
	}

	/**
	 * @param $weight
	 * @param $unit
	 *
	 * @return float|int
	 */
	public function getWeightByUnit($weight, $unit){
		if ($unit == 'kg'){
			return $weight * 1000;
		}

		return $weight;
	}

	/**
	 * @return mixed
	 */
	public function getListDimension(){
		return $this->_dimension->getListDimension();
	}

	/**
	 * @param $code
	 *
	 * @return mixed
	 */
	public function getSize($code){
		return $this->_dimension->findSizeByCode($code);
	}

	/**
	 * @return array|mixed|null
	 */
	public function getDeliveryTimeRateLink(){
		return $this->_config->get('delivery_url');
	}

	/**
	 * @return array|mixed|null
	 */
	public function getNoteLink(){
		return $this->_config->get('note_url');
	}

	/**
	 * @return \Drupal\Core\GeneratedUrl|string|null
	 */
	public function getReceivePageLink(){
		$link = $this->_config->get('receive_url');

		if (!empty($link)){
			return Url::fromUri($link)->toString();
		}

		return NULL;
	}

	/**
	 * @param bool $singapore
	 *
	 * @return array
	 */
	public function getLinkMenuTab($singapore = TRUE){
		$local   = Url::fromRoute('singpost.toolbox.calculate.singapore.index')->toString();
		$oversea = Url::fromRoute('singpost.toolbox.calculate.overseas.index')->toString();

		$links = [
			'Receive' => $this->getReceivePageLink()
		];

		if ($singapore){
			$links += ['Send' => $local];
		}else{
			$links += ['Send' => $oversea];
		}

		return $links;
	}

	/**
	 *
	 * @return array|mixed|null
	 */
	public function getTooltip(){
		$default           = $this->_config->get('default')['value'];
		$registered        = $this->_config->get('registered')['value'];
		$smartpac          = $this->_config->get('smartpac')['value'];
		$am_mail           = $this->_config->get('am_mail')['value'];
		$tracked_package   = $this->_config->get('tracked_package')['value'];
		$advice_of_receipt = $this->_config->get('advice_of_receipt')['value'];
		$doorstep_pickup   = $this->_config->get('doorstep_pickup')['value'];
		$buy_now_url       = $this->_config->get('buy_now_url');
		$buy_now_url_sp    = $this->_config->get('buy_now_url_sp');
		$book_now_url      = $this->_config->get('book_now_url');
		$minimum_price     = $this->_config->get('minimum_price')['value'];
		$book_now_tooltip  = $this->_config->get('book_now_tooltip')['value'];

		return [
			'compensation'      => [
				'default'    => $default ?? '',
				'registered' => $registered ?? '',
				'smartpac'   => $smartpac ?? '',
				'am_mail'    => $am_mail ?? '',
			],
			'tracked_package'   => $tracked_package ?? '',
			'advice_of_receipt' => $advice_of_receipt,
			'doorstep_pickup'   => $doorstep_pickup,
			'url'               => [
				'buy_now_url'    => $buy_now_url,
				'book_now_url'   => $book_now_url,
				'buy_now_url_sp' => $buy_now_url_sp
			],
			'minimum_price'     => $minimum_price,
			'book_now_tooltip'  => $book_now_tooltip
		];
	}

	/**
	 * @return mixed
	 */
	public function getErrorMessage(){
		return $this->_config->get('sr_error_message');
	}


	/**
	 * @param $data
	 *
	 * @return array
	 */
	public function formatData($data){
		$new_arr  = [];
		$storage  = $this->_delivery->getStorageData();
		$delivery = DeliveryServiceModel::COMPENSATION_LIST;

		usort($data, function ($first, $second){
			$order = ['Tracked Packaged', 'Poly', 'SmartPac', 'Speedpost'];
			foreach ($order as $value){
				if (strpos($first['DeliveryServiceName'], $value) !== FALSE){
					return 0;
				}
				if (strpos($second['DeliveryServiceName'], $value) !== FALSE){
					return 1;
				}
			}
		});

		foreach ($data as $value){
			if ($value['DeliveryServiceType'] == 'Speedpost'){
				$value['rates'] = $value['PostageCharges'];
			}else{
				$value['rates'] = $value['NetPostageCharges'];
			}

			$new_arr[$value['DeliveryServiceName']] = $value;
		}

		if (!empty($storage)){
			foreach ($storage as $item){
				if ($item['disabled'] == 1){
					unset($new_arr[$item['delivery_service_name']]);
				}
				if (!empty($new_arr[$item['delivery_service_name']])){

					if (!empty($item['service_image'])){
						$file = File::load($item['service_image']);
						$service_img_path = $file->createFileUrl();
					}
					else{
						$service_img_path = '#';
					}

					$new_arr[$item['delivery_service_name']]['display_name']      = $item['display_name'];
					$new_arr[$item['delivery_service_name']]['url']               = !empty($item['url']) ? Url::fromUri($item['url']) : '';
					$new_arr[$item['delivery_service_name']]['service_image']	  = $service_img_path;
					$new_arr[$item['delivery_service_name']]['maximum_dimension'] = $item['maximum_dimension'];
					$new_arr[$item['delivery_service_name']]['recommended']       = $item['recommended'];
					$new_arr[$item['delivery_service_name']]['compensation']      = $delivery[$item['compensation']];
					$new_arr[$item['delivery_service_name']]['is_tracked']        = $item['is_tracked'];
				}
			}
		}

		usort($new_arr, function ($first, $second){
			return floatval(str_replace('$', '', $first["rates"])) > floatval(str_replace('$', '',
					$second["rates"]));
		});

		return $new_arr;
	}

	/**
	 * @param $result
	 *
	 * @return int|mixed
	 */
	public function getMaximumPrice($result){
		if (!empty($result)){
			array_walk($result, function (&$item){
				if ($item['DeliveryServiceType'] == 'Speedpost'){
					$item = (float) ltrim($item['PostageCharges'], '$');
				}else{
					$item = (float) ltrim($item['NetPostageCharges'], '$');
				}
			});

			return '$' . max($result);
		}

		return '$0';
	}
}