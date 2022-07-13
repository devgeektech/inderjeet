<?php


namespace Drupal\singpost_toolbox_track_and_trace\Helper;


use Drupal;
use Drupal\singpost_toolbox\Helper\ToolboxBase;

/**
 * Class TrackAndTrace
 *
 * @package Drupal\singpost_toolbox_track_and_trace\Helper
 */
class TrackAndTrace extends ToolboxBase{

	public static $config_name = 'track_and_trace.config';

	public static $item_type = ['mail', 'smartpac'];

	const TRACK_AND_TRACE_TYPE_ID = 794;

	const EVENT_TRANSACTION_ID = 100300;

	const ACE_STATUS_CODE = 'RP';

	/**
	 * @param array $tracking_items
	 *
	 * @return array|mixed|\SimpleXMLElement|string
	 */
	public function getApiResults(array $tracking_items){
		$esb_url   = $this->_config->get('tnt_url_api');
		$system_id = $this->_config->get('tnt_system_id');

		$item_tracking_number = [];

		foreach ($tracking_items as $item){
			if (!trim($item)){
				continue;
			}
			$item_tracking_number[]['TrackingNumber'] = trim($item);
		}

		if (empty($item_tracking_number)){
			return [];
		}

		$root_elem = 'ItemTrackingDetailsRequest';

		$children = [
			'SystemID'            => $system_id,
			'ItemTrackingNumbers' => $item_tracking_number,
		];

		$input = $this->_generateXmlInput($root_elem, $children, FALSE);

		$result = $this->_getEsbResult($esb_url, 'xml', $input);

		if (!empty($result) && isset($result['Status']['ErrorCode'])){
			if (!empty($result['ItemsTrackingDetailList']['ItemTrackingDetail']) && $result['Status']['ErrorCode'] == '0'){
				$result = $result['ItemsTrackingDetailList']['ItemTrackingDetail'];

				if (!empty($result['TrackingNumber'])){
					$result = [$result];
				}
			}else{
				$result = [$result['Status']];
			}
		}else{
			$result = [];
		}

		return $result;
	}

	/**
	 * @param $item_type
	 * @param string $nos
	 *
	 * @return string
	 */
	public function getAlterItemType($item_type, $nos = ''){
		$type = strtolower(str_replace(' ', '-', $item_type));

		if ($type == 'mail' && (strlen($nos) == 21)){
			return 'smartpac';
		}

		return $type;
	}

	/**
	 * @return array|mixed|null
	 */
	public function getErrorMessage(){
		return $this->_config->get('tnt_error_message');
	}

	/**
	 * @return array|mixed|null
	 */
	public function getErrorTrackingMessage(){
		return $this->_config->get('tnt_error_tracking_message');
	}

	/**
	 * @return array|mixed|null
	 */
	public function getOtherNotes(){
		if ($this->_config->get('tnt_other_note')){
			return $this->_config->get('tnt_other_note')['value'];
		}

		return NULL;
	}

	/**
	 * @return array|mixed|null
	 */
	public function getNotFoundTrackingMessage(){
		if ($this->_config->get('tnt_notfound_tracking_message')){
			return $this->_config->get('tnt_notfound_tracking_message')['value'];
		}

		return NULL;
	}

	/**
	 * @return array|mixed|null
	 */
	public function getRedirectMessage(){
		return $this->_config->get('tnt_redirect_message');
	}

	/**
	 * @param $alter_track_number
	 *
	 * @return string
	 */
	public function getAlterTrackingNumber($alter_track_number){
		$alter = '';

		if (is_array($alter_track_number) && !empty($alter_track_number)){
			foreach ($alter_track_number as $value){
				$alter .= ' | ' . $value;
			}
		}else{
			$alter .= ' | ' . $alter_track_number;
		}

		return $alter;
	}

	/**
	 * @param string $status_description
	 * @param string $ace_status_code
	 * @param string $type_id
	 * @param string $event_id
	 *
	 * @return array|mixed|null
	 */
	public function getDestinationCountry(
		$status_description,
		$ace_status_code,
		$type_id,
		$event_id){
		$overseas = $this->_config->get('tnt_oversea_country');
		$local    = $this->_config->get('tnt_local_country');

		$destination = [];

		if (!empty($overseas)){
			foreach ($overseas as $oversea){
				if (strcmp($oversea['status'], $status_description) == 0){

					return $destination = ['Oversea' => $oversea['content']];
				}

				if ($ace_status_code == self::ACE_STATUS_CODE && $type_id == self::TRACK_AND_TRACE_TYPE_ID && $event_id == self::EVENT_TRANSACTION_ID && $local['content']){

					return $destination = ['Singapore' => $local['content']];
				}
			}
		}

		return $destination;
	}

	/**
	 * @param $status_description
	 *
	 * @return mixed
	 */
	public function getRedeliverStatus($status_description){
		$status_arr = Drupal::service('singpost.tnt.status.service')->getListStatus();

		if (!empty($status_arr) && $status_arr['redirect-redeliver']){
			foreach ($status_arr['redirect-redeliver'] as $value){
				$first_str = substr($value, 0, 1);
				$last_str  = substr($value, - 1, 1);

				switch ($value){
					case (strpos($value, '*') === FALSE && $value == $status_description):

						return self::getRedirectMessage();
					case ($first_str != '*' && $last_str == '*'):
						$value = substr_replace($value, '', - 1, 1);

						if (substr_compare($status_description, $value, 0, strlen($value)) == 0){
							return self::getRedirectMessage();
						}

						break;
					case ($first_str == '*' && $last_str != '*'):
						$value = substr_replace($value, '', 0, 1);

						if (substr_compare($status_description, $value, - strlen($value),
								strlen($value)) == 0){
							return self::getRedirectMessage();
						}

						break;
					case ($first_str == '*' && $last_str == '*'):
						$value = substr_replace($value, '', 0, 1);
						$value = substr_replace($value, '', - 1, 1);

						if (str_contains($status_description, $value)){
							return self::getRedirectMessage();
						}

						break;
				}
			}
		}
	}
}