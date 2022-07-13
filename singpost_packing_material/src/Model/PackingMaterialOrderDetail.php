<?php


namespace Drupal\singpost_packing_material\Model;


use Drupal;
use Drupal\singpost_base\Model;
use Exception;

/**
 * Class PackingMaterialOrderDetail
 *
 * @package Drupal\singpost_packing_material\Model
 *
 * @property int $order_id
 * @property int $product_id
 * @property float $price
 * @property float $quantity
 */
class PackingMaterialOrderDetail extends Model{

	/**
	 * @var array
	 */
	protected $_attributes = [
		'order_id', 'product_id', 'price', 'quantity'
	];

	/**
	 * @return string
	 */
	public static function tableName(){
		return 'singpost_pm_order_detail';
	}

	/**
	 * @return string
	 */
	public static function tableAlias(){
		return 'pm_od';
	}

	/**
	 * @return string
	 */
	public static function tablePrimaryKey(){
		return 'order_id';
	}

	/**
	 * @param array $values
	 *
	 * @return bool
	 */
	public function createMultiple(array $values){
		try{
			$query = $this->getConnection()->insert(static::tableName())
			              ->fields($this->_attributes);

			foreach ($values as $value){
				$query->values($value);
			}

			$query->execute();

			static::afterSave();

			return TRUE;
		}catch (Exception $e){
			Drupal::logger(static::tableName())->error($e->getMessage());

			return FALSE;
		}
	}
}