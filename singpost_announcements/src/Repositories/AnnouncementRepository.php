<?php

namespace Drupal\singpost_announcements\Repositories;

use Drupal\singpost_announcements\Model\Announcement;
use Drupal\singpost_base\Repositories\BaseRepository;
use Drupal\singpost_base\Support\Paginator;

/**
 * Interface AnnouncementRepository
 *
 * @package Drupal\singpost_announcements\Repositories
 */
interface AnnouncementRepository extends BaseRepository{

	/**
	 * @param Paginator $paginator
	 *
	 * @return Announcement[]
	 */
	public function getTableData(Paginator $paginator);

	/**
	 * @param array $announcements
	 * Array of announcements id
	 *
	 * @return Announcement[]
	 */
	public function getBulkActionAnnouncementsList(array $announcements);

	/**
	 * @return mixed
	 */
	public function getYears();
}