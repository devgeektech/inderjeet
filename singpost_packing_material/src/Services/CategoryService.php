<?php


namespace Drupal\singpost_packing_material\Services;


use Drupal\singpost_base\Services\BaseService;
use Drupal\singpost_packing_material\Repositories\CategoryRepository;

/**
 * Class PackingMaterialService
 *
 * @package Drupal\singpost_packing_material\Services
 *
 * @property \Drupal\singpost_packing_material\Model\PackingMaterialCategory $model
 */
class CategoryService extends BaseService implements CategoryRepository{

	/**
	 * @param $id
	 *
	 * @return bool|mixed
	 */
	public function existsProduct($id){
		return $this->model->existsProduct($id);
	}

	/**
	 * @return array|mixed
	 */
	public function getCategories(){
		return $this->model->getCategories();
	}

	/**
	 * @param $id
	 *
	 * @return string
	 */
	public function getNameCategoryById($id){
		return $this->model->getNameCategoryById($id);
	}

	/**
	 * @return array
	 */
	public function getAllProduct(){
		return $this->model->getAllProduct();
	}

	/**
	 * @param $id
	 * @return array
	 */
	public function getSingleProductById($id){
		return $this->model->getSingleProductById($id);
	}
}