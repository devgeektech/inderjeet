<?php


namespace Drupal\singpost_toolbox_calculate_postage\Services;


use Drupal\singpost_base\Services\BaseService;
use Drupal\singpost_toolbox_calculate_postage\Repositories\DeliveryServiceRepository;

/**
 * Class DeliveryService
 *
 * @package Drupal\singpost_toolbox_calculate_postage\Services
 *
 * @property \Drupal\singpost_toolbox_calculate_postage\Model\DeliveryServiceModel $model
 */
class DeliveryService extends BaseService implements DeliveryServiceRepository{

	/**
	 * @return mixed
	 */
	public function getListCompensation(){
		return $this->model::COMPENSATION_LIST;
	}

	/**
	 * @param $string
	 *
	 * @return bool|mixed
	 */
	public function checkExistDeliveryServiceName($string){
		$query = $this->model::find()
		                     ->condition(['delivery_service_name', $string, '='])
		                     ->one();

		return (bool) $query;
	}

	/**
	 * @return array
	 */
	public function getStorageData(){
		$storage = $this->model::find()
		                       ->select(['delivery_service_name', 'display_name', 'url', 'maximum_dimension', 'recommended', 'compensation'])
		                       ->condition(['published', $this->model::ACTIVE, '='])
		                       ->asArray()
		                       ->all();
		if (!empty($storage)){
			return $storage;
		}

		return [];
	}
}