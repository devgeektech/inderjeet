<?php

namespace Drupal\singpost_sgx_announcements\Model;

use Drupal\file\Entity\File;
use Drupal\singpost_base\Behaviors\BlameableBehavior;
use Drupal\singpost_base\Behaviors\LogBehavior;
use Drupal\singpost_base\Behaviors\TimestampBehavior;
use Drupal\singpost_base\Model;

/**
 * Class SgxAnnouncement
 *
 * @package Drupal\singpost_sgx_announcements\Model
 * @property int $id
 * @property string $title
 * @property int $date
 * @property string $file
 * @property bool $published
 * @property int $created_at
 * @property int $updated_at
 * @property int $created_by
 * @property int $updated_by
 */
class SgxAnnouncement extends Model{

	const ACTIVE = 1;

	protected $_attributes = [
		'title', 'date', 'file', 'published', 'created_at', 'updated_at', 'created_by', 'updated_by'
	];

	/**
	 * @return string
	 */
	public static function tableName(){
		return 'sgx_announcement';
	}

	/**
	 * @inheritDoc
	 */
	public function behaviors(){
		return [
			'log'       => [
				'class'     => LogBehavior::class,
				'module'    => 'singpost_sgx_announcements',
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
	 * @throws \Drupal\Core\Entity\EntityStorageException
	 */
	public function beforeSave(){
		if ($this->file){
			$file = File::load($this->file[0]);
			$file->setPermanent();
			$file->save();
			$this->file = $this->file[0];
		}else{
			$this->file = NULL;
		}

		parent::beforeSave();
	}

	/**
	 * @return mixed
	 */
	public function getYears(){
		$db    = $this->getConnection();
		$query = $db->select(static::tableName());
		$query->addExpression("YEAR(FROM_UNIXTIME(date))", "year");
		$query->condition('published', self::ACTIVE, '=');
		$query->orderBy('date', 'desc');

		return $query->execute()->fetchAllKeyed(0, 0);
	}

	/**
	 * @param $year
	 *
	 * @return mixed
	 */
	public function getListMonthOfYear($year){
		$db    = $this->getConnection();
		$query = $db->query("
		SELECT MONTH(FROM_UNIXTIME(date)) AS month, YEAR(FROM_UNIXTIME(date)) AS year 
		FROM {" . static::tableName() . "}
		WHERE published = :published and YEAR(FROM_UNIXTIME(date)) = :year
		GROUP BY date
		ORDER BY date", [':published' => self::ACTIVE, ':year' => $year]);
		$query = $query->fetchAllKeyed();
		
		$months = [];
		if (!empty($query)){
			foreach ($query as $key => $item){
				$months[$key] = date('M', mktime(0, 0, 0, $key, 1, $item));
			}
		}

		return $months;
	}
}