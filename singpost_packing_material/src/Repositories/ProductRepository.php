<?php


namespace Drupal\singpost_packing_material\Repositories;


use Drupal\singpost_base\Repositories\BaseRepository;
use Drupal\singpost_base\Support\Paginator;

/**
 * Interface ProductRepository
 *
 * @package Drupal\singpost_packing_material\Repositories
 */
interface ProductRepository extends BaseRepository{

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
	public function hasOrderDetail($id);

	/**
	 * @param $id
	 *
	 * @return mixed
	 */
	public function getListProductByCateId($id);

	/**
	 * @param $id
	 *
	 * @return mixed
	 */
	public function getListProductById($id);

	/**
	 * @param $id
	 *
	 * @return mixed
	 */
	public function getProductName($id);
}