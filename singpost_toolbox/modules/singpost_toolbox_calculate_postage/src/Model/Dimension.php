<?php

namespace Drupal\singpost_toolbox_calculate_postage\Model;

use Drupal\singpost_base\Behaviors\BlameableBehavior;
use Drupal\singpost_base\Behaviors\LogBehavior;
use Drupal\singpost_base\Behaviors\TimestampBehavior;
use Drupal\singpost_base\Model;

/**
 * Class Dimension
 *
 * @package Drupal\singpost_toolbox_calculate_postage\Model
 *
 * @property int $id
 * @property string $size_code
 * @property string $text
 * @property string $value
 * @property float $length
 * @property float $width
 * @property float $height
 * @property float $weight
 * @property int $published
 * @property int $created_at
 * @property int $updated_at
 * @property int $created_by
 * @property int $updated_by
 *
 */
class Dimension extends Model{

	const ACTIVE = 1;

	protected $_attributes = [
		'size_code',
		'text',
		'value',
		'length',
		'width',
		'height',
		'weight',
		'published',
		'created_at',
		'updated_at',
		'created_by',
		'updated_by',
	];

	/**
	 * @inheritDoc
	 */
	public static function tableName(){
		return 'singpost_dimension';
	}

	/**
	 * @inheritDoc
	 */
	public static function tableAlias(){
		return 'd';
	}

	/**
	 * @inheritDoc
	 */
	public function behaviors(){
		return [
			'log'       => [
				'class'     => LogBehavior::class,
				'module'    => 'singpost_toolbox_calculate_postage',
				'attribute' => 'size_code',
			],
			'timestamp' => [
				'class'                => TimestampBehavior::class,
				'created_at_attribute' => 'created_at',
				'updated_at_attribute' => 'updated_at',
			],
			'blameable' => [
				'class'                => BlameableBehavior::class,
				'created_by_attribute' => 'created_by',
				'updated_by_attribute' => 'updated_by',
			],
		];
	}
}