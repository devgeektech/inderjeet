<?php


namespace Drupal\singpost_packing_material\Model;


use Drupal\singpost_base\Behaviors\LogBehavior;
use Drupal\singpost_base\Model;

/**
 * Class PackingMaterialOrder
 *
 * @package Drupal\singpost_packing_material\Model
 *
 * @property int $id
 * @property string $name
 * @property string $company_name
 * @property string $email
 * @property string $contact_number
 * @property string $block_number
 * @property string $street_name
 * @property string $unit_number
 * @property string $postal_code
 * @property string $sp_account_number
 * @property float $subtotal
 * @property float $total
 * @property float $discount
 * @property int $order_date
 */
class PackingMaterialOrder extends Model{

	/**
	 * @var array
	 */
	protected $_attributes = [
		'name', 'company_name', 'email', 'contact_number',
		'block_number', 'street_name', 'unit_number', 'postal_code', 'sp_account_number',
		'subtotal', 'total', 'discount', 'order_date'
	];

	/**
	 * @return string
	 */
	public static function tableName(){
		return 'singpost_pm_order';
	}

	/**
	 * @return string
	 */
	public static function tableAlias(){
		return 'pm_o';
	}

	/**
	 * @return array
	 */
	public function behaviors(){
		return [
			'log'       => [
				'class'     => LogBehavior::class,
				'module'    => 'singpost_packing_material',
				'attribute' => 'name'
			]
		];
	}

	/**
	 * @param $id
	 *
	 * @return mixed
	 */
	public function getOrderDetail($id){

		$db = \Drupal::database();
	    $query = $db->select( PackingMaterialOrderDetail::tableName(),
			            PackingMaterialOrderDetail::tableAlias() );
	    $query->condition('order_id', $id, '=')
 	        ->fields(PackingMaterialOrderDetail::tableAlias(), array('product_id', 'price', 'quantity'))
	        ->fields(PackingMaterialProduct::tableAlias(), array('title'));
	    $query->innerJoin(PackingMaterialProduct::tableName(), 'pm_p', 'pm_p.id = pm_od.product_id');
 		$results = $query->execute()->fetchAll();
 
		return $results;

/*		return $this->getConnection()
		            ->select(PackingMaterialOrderDetail::tableName(),
			            PackingMaterialOrderDetail::tableAlias())
		            ->fields(PackingMaterialOrderDetail::tableAlias(),
			            ['product_id', 'price', 'quantity'])
		            ->condition('order_id', $id, '=')
		            ->execute()
		            ->fetchAll();*/
	}
}