<?php

namespace Drupal\singpost_announcements\Model;

use Drupal\Component\Serialization\Json;
use Drupal\singpost_base\Behaviors\BlameableBehavior;
use Drupal\singpost_base\Behaviors\LogBehavior;
use Drupal\singpost_base\Behaviors\TimestampBehavior;
use Drupal\singpost_base\Model;

/**
 * Class Announcement
 *
 * @package Drupal\singpost_announcements\Model
 *
 * @property int $id
 * @property string $title
 * @property int $start_date
 * @property int $end_date
 * @property string $summary
 * @property array $content
 * @property string $formatted_content
 * @property bool $published
 * @property int $created_at
 * @property int $updated_at
 * @property int $created_by
 * @property int $updated_by
 *
 */
class Announcement extends Model{

	const ACTIVE = 1;

	protected $_attributes = [
		'title', 'start_date', 'end_date', 'summary', 'published', 'content', 'created_at', 'updated_at', 'created_by', 'updated_by',
		'formatted_content'
	];

	/**
	 * @inheritDoc
	 */
	public static function tableName(){
		return 'announcement';
	}

	/**
	 * @inheritDoc
	 */
	public static function tableAlias(){
		return 'a';
	}

	/**
	 * @inheritDoc
	 */
	public function behaviors(){
		return [
			'log'       => [
				'class'     => LogBehavior::class,
				'module'    => 'singpost_announcements',
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

	/**
	 * @inheritDoc
	 */
	public function beforeSave(){
		if (is_array($this->content)){
			$this->formatted_content = $this->content['value'] ?? '';
			$this->content           = Json::encode($this->content);
		}

		parent::beforeSave();
	}

	/**
	 * @inheritDoc
	 */
	public function afterFind(){
		if (!empty($this->content) && is_string($this->content)){
			$this->content = Json::decode($this->content);
		}

		parent::afterFind();
	}
	

	/**
	 * @return mixed
	 */
	public function getYears(){
		$db    = $this->getConnection();
		$query = $db->select(static::tableName(), static::tableAlias());
		$query->addExpression("YEAR(FROM_UNIXTIME(start_date))", "year");
		$query->condition('published', self::ACTIVE, '=');
		$query->orderBy('start_date', 'desc');

		return $query->execute()->fetchAllKeyed(0, 0);
	}
	
	
	/**
	 * @inheritDoc
	 */
	public function getAllAnnoucement(){
		// $db    = $this->getConnection();
		$query = \Drupal::database();
		$result = $query->query("SELECT * FROM {announcement}");
		
		return $result->fetchAll();
	}
	
	/**
	 * @inheritDoc
	 */
	public function singleAnnoucement($id){
		$query = \Drupal::database();
		$result = $query->query("SELECT * FROM {announcement} WHERE id = ".$id."");
		return $result->fetchAll();
	}

}