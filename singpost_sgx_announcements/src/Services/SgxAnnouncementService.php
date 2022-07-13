<?php

namespace Drupal\singpost_sgx_announcements\Services;

use Drupal\singpost_base\Services\BaseService;
use Drupal\singpost_sgx_announcements\Model\SgxAnnouncement;
use Drupal\singpost_sgx_announcements\Repositories\SgxAnnouncementRepository;

/**
 * Class SgxAnnouncementService
 *
 * @package Drupal\singpost_sgx_announcement\Services
 *
 * @property SgxAnnouncement $model
 */
class SgxAnnouncementService extends BaseService implements SgxAnnouncementRepository{

	/**
	 * @inheritDoc
	 */
	public function getBulkActionSgxAnnouncementsList(array $sgx_announcements){
		return $this->model::findAll(['id', $sgx_announcements, 'IN']);
	}

	/**
	 * @return array|\Drupal\singpost_base\Model[]
	 */
	public function getYears(){
		return $this->model->getYears();
	}

	/**
	 * @param $year
	 *
	 * @return mixed
	 */
	public function getListMonthOfYear($year){
		return $this->model->getListMonthOfYear($year);
	}
}