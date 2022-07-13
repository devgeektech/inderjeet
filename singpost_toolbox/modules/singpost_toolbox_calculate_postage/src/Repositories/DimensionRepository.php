<?php

namespace Drupal\singpost_toolbox_calculate_postage\Repositories;

use Drupal\singpost_base\Repositories\BaseRepository;
use Drupal\singpost_base\Support\Paginator;
use Drupal\singpost_toolbox_calculate_postage\Model\Dimension;

/**
 * Interface DimensionRepository
 *
 * @package Drupal\singpost_toolbox_calculate_postage\Repositories
 */
interface DimensionRepository extends BaseRepository{

	/**
	 * @param Paginator $paginator
	 *
	 * @return Dimension[]
	 */
	public function getTableData(Paginator $paginator);

	/**
	 * @return mixed
	 */
	public function getListDimension();

	/**
	 * @param $code
	 *
	 * @return mixed
	 */
	public function findSizeByCode($code);
}