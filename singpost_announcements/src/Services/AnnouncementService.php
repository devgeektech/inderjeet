<?php

namespace Drupal\singpost_announcements\Services;

use Drupal\singpost_announcements\Model\Announcement;
use Drupal\singpost_announcements\Repositories\AnnouncementRepository;
use Drupal\singpost_base\Services\BaseService;

/**
 * @property Announcement $model
 */
class AnnouncementService extends BaseService implements AnnouncementRepository{

	/**
	 * @inheritDoc
	 */
	public function getBulkActionAnnouncementsList(array $announcements){
		return $this->model::findAll(['id', $announcements, 'IN']);
	}


	/**
	 * @return mixed
	 */
	public function getYears(){
		return $this->model->getYears();
	}
}