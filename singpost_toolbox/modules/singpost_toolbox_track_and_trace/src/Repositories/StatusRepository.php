<?php


namespace Drupal\singpost_toolbox_track_and_trace\Repositories;


use Drupal\singpost_base\Repositories\BaseRepository;

/**
 * Interface StatusRepository
 *
 * @package Drupal\singpost_toolbox_track_and_trace\Repositories
 */
interface StatusRepository extends BaseRepository{

	/**
	 * @return mixed
	 */
	public function getListStatus();
}