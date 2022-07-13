<?php


namespace Drupal\singpost_packing_material\Repositories;


use Drupal\singpost_base\Repositories\BaseRepository;
use Drupal\singpost_base\Support\Paginator;

/**
 * Interface PackingMaterialRepository
 *
 * @package Drupal\singpost_packing_material\Repositories
 */
interface CategoryRepository extends BaseRepository{

	/**
	 * @param \Drupal\singpost_base\Support\Paginator $paginator
	 *
	 * @return \Drupal\singpost_base\ModelInterface[]
	 */
	public function getTableData(Paginator $paginator);

	/**
	 * @param $id
	 *
	 * @return mixed
	 */
	public function existsProduct($id);

	/**
	 * @return mixed
	 */
	public function getCategories();

	/**
	 * @param $id
	 *
	 * @return mixed
	 */
	public function getNameCategoryById($id);

	/**
	 * @return array
	 */
	public function getAllProduct();
}