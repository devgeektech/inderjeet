<?php

namespace Drupal\singpost_toolbox_calculate_postage\Services;

use Drupal\singpost_base\Services\BaseService;
use Drupal\singpost_base\Support\ArrayHelper;
use Drupal\singpost_toolbox_calculate_postage\Model\Dimension;
use Drupal\singpost_toolbox_calculate_postage\Repositories\DimensionRepository;

/**
 * @property Dimension $model
 */
class DimensionService extends BaseService implements DimensionRepository{

	/**
	 * @return mixed|void
	 */
	public function getListDimension(){
		return ArrayHelper::map($this->model::find()
		                                    ->condition(['published', $this->model::ACTIVE, '='])
		                                    ->orderBy('weight', 'ASC')
		                                    ->asArray()
		                                    ->all(), 'size_code', 'text');
	}

	/**
	 * @param $code
	 *
	 * @return \Drupal\singpost_base\Model|mixed
	 */
	public function findSizeByCode($code){
		return $this->model::find(['size_code', 'length', 'width', 'height'])
		                   ->condition(['size_code', $code, '='])
		                   ->one();
	}
}