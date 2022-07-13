<?php


namespace Drupal\singpost_publications\Model;


use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\Html;
use Drupal\file\Entity\File;
use Drupal\singpost_base\Behaviors\BlameableBehavior;
use Drupal\singpost_base\Behaviors\LogBehavior;
use Drupal\singpost_base\Behaviors\TimestampBehavior;
use Drupal\singpost_base\Model;

/**
 * Class Publication
 *
 * @package Drupal\singpost_publications\Model
 *
 * @property int $id
 * @property string $title
 * @property string $image_thumbnail
 * @property string $micro_site_cta_title
 * @property string $micro_site_cta_url
 * @property string $micro_link_type
 * @property string $annual_report
 * @property string $sustainability_report
 * @property array $content
 * @property int $published_at
 * @property bool $published
 * @property int $created_at
 * @property int $updated_at
 * @property int $created_by
 * @property int $updated_by
 */
class Publication extends Model{

	const ACTIVE = 1;

	/**
	 * @var array
	 */
	protected $_attributes = [
		'title', 'image_thumbnail','micro_site_cta_title', 'micro_site_cta_url', 'micro_link_type', 'annual_report', 'sustainability_report', 'content', 'published_at', 'published', 'created_at', 'updated_at', 'created_by', 'updated_by', 'summary', 'heading', 'sub_heading', 'slug'
	];

	/**
	 * @return string
	 */
	public static function tableName(){
		return 'publications';
	}

	/**
	 * @return string
	 */
	public static function tableAlias(){
		return 'p';
	}

	/**
	 * @return array
	 */
	public function behaviors(){
		return [
			'log'       => [
				'class'     => LogBehavior::class,
				'module'    => 'singpost_publications',
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
		$list_file = [];

		if ($this->image_thumbnail){
			$list_file += ['image_thumbnail' => $this->image_thumbnail];
		}else{
			$this->image_thumbnail = NULL;
		}

		if ($this->annual_report){
			$list_file += ['annual_report' => $this->annual_report];
		}else{
			$this->annual_report = NULL;
		}

		if ($this->sustainability_report){
			$list_file += ['sustainability_report' => $this->sustainability_report];
		}else{
			$this->sustainability_report = NULL;
		}

		if (!empty($list_file)){
			foreach ($list_file as $key => $value){
				$file = File::load($value[0]);
				$file->setPermanent();
				$file->save();
				$this->$key = $value[0];
			}
		}

		if (is_array($this->content)){
			$data = [];
			foreach ($this->content as $key => $item){
				if (!empty($item['label']) || $item['file']){
					$item['label'] = Html::escape($item['label']);

					if ($item['file']){
						$file = File::load($item['file'][0]);
						$file->setPermanent();
						$file->save();
						$item['file'] = $item['file'][0];
					}else{
						$item['file'] = NULL;
					}

					$data += [$key => $item];
				}
			}

			if (!empty($data)){
				$data = array_values($data);
			}

			$this->content = Json::encode($data);
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
	 * @return array
	 */
	public function getYears(){
		$db    = $this->getConnection();
		$query = $db->select(static::tableName(), static::tableAlias());
		$query->addExpression("published_at", "year");
		$query->condition('published', self::ACTIVE, '=');
		$query->orderBy('published_at', 'desc');
		$results = $query->execute()->fetchAllKeyed(0, 0);
		$years   = [];

		if (!empty($results)){
			foreach ($results as $result){
				$years[$result] = 'FY' . $result . '/' . substr(($result + 1), - 2);
			}
		}

		return $years;
	}

	public function singlepublication($slug){
		$query = \Drupal::database();
		$result = $query->query("SELECT * FROM {publications} WHERE slug = '$slug'");
		return $result->fetchAll();
	}
}