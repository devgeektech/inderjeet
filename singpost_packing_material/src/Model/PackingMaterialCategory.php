<?php


namespace Drupal\singpost_packing_material\Model;


use Drupal\file\Entity\File;
use Drupal\singpost_base\Behaviors\BlameableBehavior;
use Drupal\singpost_base\Behaviors\LogBehavior;
use Drupal\singpost_base\Behaviors\TimestampBehavior;
use Drupal\singpost_base\Model;

/**
 * Class PackingMaterialCategory
 *
 * @package Drupal\singpost_packing_material\Model
 *
 * @property int $id
 * @property string $title
 * @property string $feature_img
 * @property bool $published
 * @property int $weight
 * @property int $created_at
 * @property int $updated_at
 * @property int $created_by
 * @property int $updated_by
 */
class PackingMaterialCategory extends Model{

	const ACTIVE = 1;

	/**
	 * @var array
	 */
	protected $_attributes = [
		'title', 'feature_img', 'published', 'weight', 'created_at', 'updated_at', 'created_by', 'updated_by'
	];

	/**
	 * @return string
	 */
	public static function tableName(){
		return 'singpost_pm_category';
	}

	/**
	 * @return string
	 */
	public static function tableAlias(){
		return 'pm_c';
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
		if ($this->feature_img){
			$file = File::load($this->feature_img[0]);
			$file->setPermanent();
			$file->save();
			$this->feature_img = $this->feature_img[0];
		}

		parent::beforeSave();
	}

	/**
	 * @param $id
	 *
	 * @return bool
	 */
	public function existsProduct($id){
		$query = $this->getConnection()
		              ->select(PackingMaterialProduct::tableName(),
			              PackingMaterialProduct::tableAlias())
		              ->fields(PackingMaterialProduct::tableAlias(), ['category_id'])
		              ->condition('category_id', $id, '=')
		              ->execute()
		              ->fetchField();

		return (bool) $query;
	}

	/**
	 * @return array
	 */
	public function getCategories(){
		$query = static::find()->condition(['published', self::ACTIVE, '='])
		               ->orderBy('weight', 'asc')->all();

		$results = [];

		if (!empty($query)){
			foreach ($query as $item){
				$results += [$item->id => $item->title];
			}
		}

		return $results;
	}

	/**
	 * @param $id
	 *
	 * @return string
	 */
	public function getNameCategoryById($id){
		$query = static::findOne(['id', $id]);

		if (!$query){
			return 'Category not found';
		}

		return $query->title;
	}

	/**
	 * @param $image_id
	 *
	 * @return string
	 */
	public function getImage($image_id){
		$file = File::load($image_id);

		if (!$file){
			return 'Images not exists';
		}

		return $file->createFileUrl();
	}

	/**
	 * @return array
	 */
	public function getAllProduct(){
		$categories = $this->getCategories();
		$product    = new PackingMaterialProduct();

		$results = [];

		if (!empty($categories)){
			foreach ($categories as $key => $value){
				$products = $product->getListProductByCateId($key);
				$category = static::findOne(['id', $key]);

				$results[] = [
					'category_id'   => $key,
					'category_name' => $value,
					'feature_img'   => $category ? $this->getImage($category->feature_img) : '#',
					'products'      => $products
				];
			}
		}

		return $results;
	}

	/**
	 * @param $id
	 * @return array
	*/
	public function getSingleProductById($id){
		//$categories = $this->getCategories();
		$product    = new PackingMaterialProduct();
		$results = [];

		if (!empty($product)){

				$products = $product->getListProductById($id);			 
				foreach ($products as $product){
					$products[0]->category_name = $this->getNameCategoryById($product->category_id);
				}
 				$product_all = $product->getListProductByCateId($products[0]->category_id);	
 				foreach ($product_all as $key => $value) {
 					$product_all[$key]->category_name = $this->getNameCategoryById($value->category_id);
 				}

				$results[] = [
					//'category_id'   => $category,
 					'products'         => $products,
 					'product_all'      => $product_all

				];			
		}
		return $results;
	}
}