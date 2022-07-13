<?php


namespace Drupal\singpost_packing_material\Services;


use Drupal\singpost_base\Services\BaseService;
use Drupal\singpost_packing_material\Repositories\ProductRepository;

/**
 * Class ProductService
 *
 * @package Drupal\singpost_packing_material\Services
 *
 * @property \Drupal\singpost_packing_material\Model\PackingMaterialProduct $model
 */
class ProductService extends BaseService implements ProductRepository{

	/**
	 * @param $id
	 *
	 * @return bool|mixed
	 */
	public function hasOrderDetail($id){
		return $this->model->hasOrderDetail($id);
	}

	/**
	 * @param $id
	 *
	 * @return array|\Drupal\singpost_base\Model[]
	 */
	public function getListProductByCateId($id){
		return $this->model->getListProductByCateId($id);
	}

	/**
	 * @param $id
	 *
	 * @return array|\Drupal\singpost_base\Model[]
	 */
	public function getListProductById($id){
		return $this->model->getListProductById($id);
	}

	/**
	 * @param $id
	 *
	 * @return mixed|string
	 */
	public function getProductName($id){
		return $this->model->getProductName($id);
	}
}