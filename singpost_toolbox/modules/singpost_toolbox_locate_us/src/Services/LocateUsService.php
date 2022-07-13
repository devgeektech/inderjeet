<?php

namespace Drupal\singpost_toolbox_locate_us\Services;

use Drupal\singpost_base\Services\BaseService;
use Drupal\singpost_toolbox_locate_us\Model\LocateUsType;
use Drupal\singpost_toolbox_locate_us\Repositories\LocateUsRepository;

/**
 * @property LocateUsType $model
 */
class LocateUsService extends BaseService implements LocateUsRepository{

	/**
	 * @inheritDoc
	 */
	public function getBulkActionLocateUsList(array $locate_us_type){
		return $this->model::findAll(['id', $locate_us_type, 'IN']);
	}

}