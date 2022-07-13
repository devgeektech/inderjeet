<?php

namespace Drupal\singpost_toolbox_locate_us\Helper;

use Drupal\singpost_toolbox\Helper\ToolboxBase;
use Drupal\singpost_toolbox_locate_us\Model\LocateUsType;

/**
 * Class LocateUsType
 *
 * @package Drupal\singpost_toolbox_locate_us\Helper
 */
class LocateUs extends ToolboxBase{

	public static $config_name = 'locate_us.config';

	/**
	 * @param $type
	 * @param $postalcode
	 *
	 * @return array|mixed|\SimpleXMLElement|string
	 */
	public function getLocate($type, $postalcode){
		$postalcode = str_replace(' ', '%20', $postalcode);
		if (empty($postalcode)){
			$postalcode = '408600';
		}
		$outresults = $this->_config->get('locate_us_limt_item');

		$esb_url = $this->_config->get('locate_us_url') . '?postalcode=' . $postalcode . '&outlettype=' . $type . '&outlets=' . $outresults;

		$data = $this->_getEsbResult($esb_url, 'json', $esb_url, 'Locate Us', 'GET');

		if (!empty($data)){
			return $data;
		}

		return NULL;
	}

	/**
	 * @param $id
	 *
	 * @return mixed|null
	 */
	public static function formatType($id){
		$data = LocateUsType::findOne($id);
		if (!empty($data)){
			return strtolower(str_replace(' ', '', trim($data->value)));
		}

		return NULL;
	}

}