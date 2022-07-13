<?php

namespace Drupal\singpost_sgx_announcements\Repositories;

use Drupal\singpost_base\Repositories\BaseRepository;
use Drupal\singpost_base\Support\Paginator;
use Drupal\singpost_sgx_announcements\Model\SgxAnnouncement;

/**
 * Interface SgxAnnouncementRepository
 *
 * @package Drupal\singpost_sgx_announcements\Repositories
 */
interface SgxAnnouncementRepository extends BaseRepository{

	/**
	 * @param \Drupal\singpost_base\Support\Paginator $paginator
	 *
	 * @return SgxAnnouncement[]
	 */
	public function getTableData(Paginator $paginator);

	/**
	 * @param array $sgx_announcements
	 * Array of sgx_announcements id
	 *
	 * @return SgxAnnouncement[]
	 */
	public function getBulkActionSgxAnnouncementsList(array $sgx_announcements);

	/**
	 * @return mixed
	 */
	public function getYears();

	/**
	 * @param $year
	 *
	 * @return mixed
	 */
	public function getListMonthOfYear($year);
}