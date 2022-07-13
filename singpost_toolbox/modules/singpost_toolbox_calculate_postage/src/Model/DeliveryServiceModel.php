<?php


namespace Drupal\singpost_toolbox_calculate_postage\Model;

use Drupal\file\Entity\File;
use Drupal\singpost_base\Behaviors\BlameableBehavior;
use Drupal\singpost_base\Behaviors\LogBehavior;
use Drupal\singpost_base\Behaviors\TimestampBehavior;
use Drupal\singpost_base\Model;

/**
 * Class DeliveryServiceModel
 *
 * @package Drupal\singpost_toolbox_calculate_postage\Model
 *
 * @property int $id
 * @property string delivery_service_name,
 * @property string display_name,
 * @property string url,
 * @property string service_image,
 * @property string maximum_dimension,
 * @property int recommended,
 * @property string compensation,
 * @property int disabled,
 * @property int is_tracked,
 * @property bool $published
 * @property int $created_at
 * @property int $updated_at
 * @property int $created_by
 * @property int $updated_by
 */
class DeliveryServiceModel extends Model{

	const ACTIVE = 1;

	const COMPENSATION_LIST = [
		0 => 'None',
		1 => 'Registered',
		2 => 'Smartpac',
		3 => 'A.M Mail'
	];

	protected $_attributes = [
		'delivery_service_name',
		'display_name',
		'url',
		'service_image',
		'maximum_dimension',
		'recommended',
		'compensation',
		'is_tracked',
		'disabled',
		'published',
		'created_at',
		'updated_at',
		'created_by',
		'updated_by'
	];

	/**
	 * @return string
	 */
	public static function tableName(){
		return 'singpost_delivery_service';
	}

	/**
	 * @return string
	 */
	public static function tableAlias(){
		return 'cds';
	}

	/**
	 * @return array
	 */
	public function behaviors(){
		return [
			'log'       => [
				'class'     => LogBehavior::class,
				'module'    => 'singpost_toolbox_calculate_postage',
				'attribute' => 'delivery_service_name'
			],
			'timestamp' => [
				'class'                => TimestampBehavior::class,
				'created_at_attribute' => 'created_at',
				'updated_at_attribute' => 'updated_at'
			],
			'blameable' => [
				'class'                => BlameableBehavior::class,
				'created_by_attribute' => 'created_by',
				'updated_by_attribute' => 'updated_by'
			]
		];
	}

	public function beforeSave(){
		if ($this->service_image){
			$file = File::load($this->service_image[0]);
			$file->setPermanent();
			$file->save();
			$this->service_image = $this->service_image[0];
		}else{
			$this->service_image = NULL;
		}

		parent::beforeSave();
	}
}