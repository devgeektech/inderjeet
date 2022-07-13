<?php


namespace Drupal\singpost_packing_material\Services;


use Drupal\singpost_base\Services\BaseService;
use Drupal\singpost_packing_material\Repositories\OrderRepository;

/**
 * Class OrderService
 *
 * @package Drupal\singpost_packing_material\Services
 *
 * @property \Drupal\singpost_packing_material\Model\PackingMaterialOrder $model
 */
class OrderService extends BaseService implements OrderRepository{

	/**
	 * @param $id
	 *
	 * @return mixed
	 */
	public function getOrderDetail($id){
		return $this->model->getOrderDetail($id);
	}
}