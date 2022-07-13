<?php

namespace Drupal\singpost_base\Repositories;

use Drupal\singpost_base\ModelInterface;
use Drupal\singpost_base\Support\Paginator;

/**
 * Interface BaseRepository
 *
 * @package Drupal\singpost_base\Repositories
 */
interface BaseRepository{

	/**
	 * @return ModelInterface
	 */
	public function getModel();

	/**
	 * @param array $header
	 * @param int $limit
	 *
	 * @return Paginator
	 */
	public function getTablePaginatedData(array $header, int $limit = 15);

	/**
	 * @param Paginator $paginator
	 *
	 * @return ModelInterface[]
	 */
	public function getTableData(Paginator $paginator);

	/**
	 * @param array $params
	 *
	 * @return static
	 */
	public function applyFilters(array $params);
}