<?php


namespace Drupal\singpost_publications\Services;


use Drupal\singpost_base\Services\BaseService;
use Drupal\singpost_publications\Repositories\PublicationRepository;

/**
 * Class PublicationService
 *
 * @package Drupal\singpost_publications\Services
 *
 * @property \Drupal\singpost_publications\Model\Publication $model
 */
class PublicationService extends BaseService implements PublicationRepository{

	/**
	 * @inheritDoc
	 */
	public function getBulkActionPublicationList(array $publications){
		return $this->model::findAll(['id', $publications, 'IN']);
	}

	/**
	 * @return array|mixed
	 */
	public function getYears(){
		return $this->model->getYears();
	}
}