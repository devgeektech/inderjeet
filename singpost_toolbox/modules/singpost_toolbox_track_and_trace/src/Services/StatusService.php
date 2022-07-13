<?php


namespace Drupal\singpost_toolbox_track_and_trace\Services;


use Drupal\singpost_base\Services\BaseService;
use Drupal\singpost_base\Support\ArrayHelper;
use Drupal\singpost_toolbox_track_and_trace\Repositories\StatusRepository;

/**
 * Class StatusService
 *
 * @package Drupal\singpost_toolbox_trace_and_trace\Services
 *
 * @property \Drupal\singpost_toolbox_track_and_trace\Model\Status $model
 */
class StatusService extends BaseService implements StatusRepository{

	/**
	 * @return array
	 */
	public function getListStatus(){
		return ArrayHelper::map($this->model::find()->asArray()->all(), 'id', 'content', 'type');
	}
}