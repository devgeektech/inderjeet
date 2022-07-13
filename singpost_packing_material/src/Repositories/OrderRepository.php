<?php


namespace Drupal\singpost_packing_material\Repositories;


use Drupal\singpost_base\Repositories\BaseRepository;
use Drupal\singpost_base\Support\Paginator;

/**
 * Interface OrderRepository
 *
 * @package Drupal\singpost_packing_material\Repositories
 */
interface OrderRepository extends BaseRepository{

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
	public function getOrderDetail($id);
}