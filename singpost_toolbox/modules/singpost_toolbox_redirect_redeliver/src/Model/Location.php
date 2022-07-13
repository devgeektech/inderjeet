<?php

namespace Drupal\singpost_toolbox_redirect_redeliver\Model;

use Drupal\singpost_base\Model;
use Drupal\singpost_base\Support\ArrayHelper;

/**
 * Class Location
 *
 * @package Drupal\singpost_toolbox_redirect_redeliver\Model
 *
 *
 */
class Location extends Model{

	const ACTIVE = 1;

	protected $_attributes = [
		'title', 'value'
	];

	/**
	 * @inheritDoc
	 */
	public static function tableName(){
		return 'singpost_rr_location';
	}

	/**
	 * @inheritDoc
	 */
	public static function tableAlias(){
		return 'l';
	}

	/**
	 * @return array
	 */
	public function getLocations(){
		return ArrayHelper::map(static::find()
		                              ->orderBy('title', 'ASC')
		                              ->asArray()
		                              ->all(), 'value', 'title');
	}
}