<?php


namespace Drupal\singpost_toolbox_calculate_postage\Repositories;


use Drupal\singpost_base\Repositories\BaseRepository;

/**
 * Interface DeliveryServiceRepository
 *
 * @package Drupal\singpost_toolbox_calculate_postage\Repositories
 */
interface DeliveryServiceRepository extends BaseRepository{

	/**
	 * @return mixed
	 */
	public function getListCompensation();

	/**
	 * @param $string
	 *
	 * @return mixed
	 */
	public function checkExistDeliveryServiceName($string);

	/**
	 * @return mixed
	 */
	public function getStorageData();
}