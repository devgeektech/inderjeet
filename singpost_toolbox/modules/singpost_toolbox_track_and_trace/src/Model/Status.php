<?php


namespace Drupal\singpost_toolbox_track_and_trace\Model;


use Drupal\singpost_base\Behaviors\BlameableBehavior;
use Drupal\singpost_base\Behaviors\LogBehavior;
use Drupal\singpost_base\Behaviors\TimestampBehavior;
use Drupal\singpost_base\Model;

/**
 * Class Status
 *
 * @package Drupal\singpost_toolbox_track_and_trace\Model
 *
 * @property int $id
 * @property string $type
 * @property string $content
 * @property bool $published
 * @property int $created_at
 * @property int $updated_at
 * @property int $created_by
 * @property int $updated_by
 */
class Status extends Model{

	const ACTIVE = 1;

	protected $_attributes = [
		'type', 'content', 'published', 'created_at', 'updated_at', 'created_by', 'updated_by'
	];

	/**
	 * @return string
	 */
	public static function tableName(){
		return 'singpost_tnt_status';
	}

	/**
	 * @return string
	 */
	public static function tableAlias(){
		return 'tnts';
	}

	/**
	 * @return array
	 */
	public function behaviors(){
		return [
			'log'       => [
				'class'     => LogBehavior::class,
				'module'    => 'singpost_toolbox_track_and_trace',
				'attribute' => 'title'
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
}