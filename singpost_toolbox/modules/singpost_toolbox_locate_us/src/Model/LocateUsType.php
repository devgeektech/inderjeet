<?php

namespace Drupal\singpost_toolbox_locate_us\Model;

use Drupal\file\Entity\File;
use Drupal\singpost_base\Behaviors\BlameableBehavior;
use Drupal\singpost_base\Behaviors\LogBehavior;
use Drupal\singpost_base\Behaviors\TimestampBehavior;
use Drupal\singpost_base\Model;

/**
 * Class LocateUsType
 *
 * @package Drupal\singpost_toolbox_locate_us\Model
 *
 * @property int $id
 * @property string $title
 * @property string $value
 * @property int $icon
 * @property string $icon_text
 * @property string $marker
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 * @property int $created_by
 * @property int $updated_by
 *
 */
class LocateUsType extends Model{

	const ACTIVE = 1;

	public $tmp_icon = [];

	protected $_attributes = [
		'title',
		'value',
		'icon',
		'icon_text',
		'marker',
		'status',
		'created_at',
		'updated_at',
		'created_by',
		'updated_by',
	];

	/**
	 * @inheritDoc
	 */
	public static function tableName(){
		return 'singpost_locate_us_type';
	}

	/**
	 * @inheritDoc
	 */
	public static function tableAlias(){
		return 'l';
	}

	/**
	 * @inheritDoc
	 */
	public function behaviors(){
		return [
			'log'       => [
				'class'     => LogBehavior::class,
				'module'    => 'singpost_toolbox_locate_us',
				'attribute' => 'title',
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

	/**
	 * @throws \Drupal\Core\Entity\EntityStorageException
	 */
	public function beforeSave(){
		$this->icon   = $this->storeImage($this->icon);
		$this->marker = $this->storeImage($this->marker);

		parent::beforeSave();
	}

	/**
	 * @param $image
	 *
	 * @return bool|int
	 * @throws \Drupal\Core\Entity\EntityStorageException
	 */
	public function storeImage($image){
		if (!empty($image)){
			$file = File::load($image[0]);
			$file->setPermanent();

			$file->save();

			return $image = $image[0];
		}

		return NULL;
	}

	/**
	 * @param $id
	 *
	 * @return string|null
	 */
	public function getImage($id){
		if (!empty($id)){
			$icon_file = File::load($id);

			return file_url_transform_relative(file_create_url($icon_file->getFileUri()));
		}

		return NULL;
	}

	/**
	 * @return array
	 */
	public function getTypes(){
		$query = self::findAll(['status', 1]);

		if (!empty($query)){
			foreach ($query as $data){
				if ($data->icon){
					$this->tmp_icon[$data->id] = File::load($data->icon)->createFileUrl();
				}
			}
		}

		return $query;
	}

	/**
	 * @return mixed
	 */
	public function getListType(){
		return self::find()
		           ->select(['value', 'title'])
		           ->condition(['status', self::ACTIVE, '='])
		           ->orderBy('title', 'ASC')
		           ->fetchAllKeyed();
	}
}