<?php

namespace Drupal\singpost_toolbox_locate_us\Repositories;

use Drupal\singpost_base\Repositories\BaseRepository;
use Drupal\singpost_base\Support\Paginator;
use Drupal\singpost_toolbox_locate_us\Model\LocateUsType;

/**
 * Interface LocateUsRepository
 *
 * @package Drupal\singpost_toolbox_locate_us\Repositories
 */
interface LocateUsRepository extends BaseRepository{

	/**
	 * @param Paginator $paginator
	 *
	 * @return LocateUsType[]
	 */
	public function getTableData(Paginator $paginator);

	/**
	 * @param array $locate_us_type
	 * Array of $locate_us_type id
	 *
	 * @return LocateUsType[]
	 */
	public function getBulkActionLocateUsList(array $locate_us_type);

}