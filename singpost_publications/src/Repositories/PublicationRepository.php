<?php

namespace Drupal\singpost_publications\Repositories;

use Drupal\singpost_base\Repositories\BaseRepository;
use Drupal\singpost_base\Support\Paginator;
use Drupal\singpost_publications\Model\Publication;

/**
 * Interface PublicationRepository
 *
 * @package Drupal\singpost_publications\Repositories
 */
interface PublicationRepository extends BaseRepository{

	/**
	 * @param \Drupal\singpost_base\Support\Paginator $paginator
	 *
	 * @return Publication[]
	 */
	public function getTableData(Paginator $paginator);

	/**
	 * @param array $publications
	 * Array of publications id
	 *
	 * @return Publication[]
	 */
	public function getBulkActionPublicationList(array $publications);

	/**
	 * @return mixed
	 */
	public function getYears();
}