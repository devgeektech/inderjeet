<?php


namespace Drupal\singpost_packing_material\Model;


use Drupal\Component\Serialization\Json;
use Drupal\file\Entity\File;
use Drupal\singpost_base\Behaviors\BlameableBehavior;
use Drupal\singpost_base\Behaviors\LogBehavior;
use Drupal\singpost_base\Behaviors\TimestampBehavior;
use Drupal\singpost_base\Model;

/**
 * Class PackingMaterialProduct
 *
 * @package Drupal\singpost_packing_material\Model
 *
 * @property int $id
 * @property int $category_id
 * @property string $title
 * @property string $dimension
 * @property string $estimated_weight
 * @property string $unit
 * @property float $price
 * @property float $discounted_price
 * @property string $tooltip_text
 * @property string $product_img
 * @property float $bundle
 * @property string $description_bundle
 * @property bool $published
 * @property int $weight
 * @property int $created_at
 * @property int $updated_at
 * @property int $created_by
 * @property int $updated_by
 */
class PackingMaterialProduct extends Model{

	const ACTIVE = 1;

	public $image_url;

	/**
	 * @var array
	 */
	protected $_attributes = [
		'category_id', 'title', 'dimension', 'estimated_weight', 'unit', 'price', 'discounted_price',
		'tooltip_text', 'product_img', 'bundle', 'description_bundle',
		'published', 'weight', 'created_at', 'updated_at', 'created_by', 'updated_by'
	];

	/**
	 * @return string
	 */
	public static function tableName(){
		return 'singpost_pm_product';
	}

	/**
	 * @return string
	 */
	public static function tableAlias(){
		return 'pm_p';
	}

	/**
	 * @inheritDoc
	 */
	public function behaviors(){
		return [
			'log'       => [
				'class'     => LogBehavior::class,
				'module'    => 'singpost_packing_material',
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
		if ($this->product_img){
			$file = File::load($this->product_img[0]);
			$file->setPermanent();
			$file->save();
			$this->product_img = $this->product_img[0];
		}

		if (is_array($this->tooltip_text)){
			$this->tooltip_text = Json::encode($this->tooltip_text);
		}

		parent::beforeSave();
	}

	/**
	 * @inheritDoc
	 */
	public function afterFind(){
		if (!empty($this->tooltip_text) && is_string($this->tooltip_text)){
			$this->tooltip_text = Json::decode($this->tooltip_text);
		}

		if (!empty($this->product_img)){
			$this->image_url = File::load($this->product_img)->createFileUrl(FALSE);
		}else{
			$this->image_url = '#';
		}

		parent::afterFind();
	}

	/**
	 * @param $id
	 *
	 * @return bool
	 */
	public function hasOrderDetail($id){
		$query = $this->getConnection()
		              ->select(PackingMaterialOrderDetail::tableName(),
			              PackingMaterialOrderDetail::tableAlias())
		              ->fields(PackingMaterialOrderDetail::tableAlias(), ['product_id'])
		              ->condition('product_id', $id, '=')
		              ->execute()
		              ->fetchField();

		return (bool) $query;
	}

	/**
	 * @param int $id
	 *
	 * @return array|\Drupal\singpost_base\Model[]
	 */
	public function getListProductByCateId(int $id){
		$products = static::find()
		                  ->condition(['category_id', $id, '='])
		                  ->condition(['published', self::ACTIVE, '='])
		                  ->orderBy('weight', 'asc')
		                  ->all();

		if (!empty($products)){
			foreach ($products as $product){
				$product->afterFind();
			}

			return $products;
		}

		return FALSE;
	}

	/**
	 * @param int $id
	 *
	 * @return array|\Drupal\singpost_base\Model[]
	 */
	public function getListProductById(int $id){
		$products = static::find()
		                  ->condition(['id', $id, '='])
		                  ->condition(['published', self::ACTIVE, '='])
 		                  ->all();

		if (!empty($products)){
			foreach ($products as $product){
				$product->afterFind();
			}

			return $products;
		}

		return FALSE;
	}

	/**
	 * @param $id
	 *
	 * @return string
	 */
	public function getProductName($id){
		$query = $this::findOne(['id', $id]);

		if (!$query){
			return 'Product not found';
		}

		return $query->title;
	}
}