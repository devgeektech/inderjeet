<?php

namespace Drupal\singpost_base\Behaviors;

use Drupal;
use Drupal\singpost_base\Behavior;
use Drupal\singpost_base\Model;
use Drupal\singpost_base\ModelInterface;

/**
 * Class TimestampBehavior
 *
 * @package Drupal\singpost_base\Behaviors
 */
class TimestampBehavior extends Behavior{

	/**
	 * @var string
	 */
	public $created_at_attribute = 'created_at';

	/**
	 * @var string
	 */
	public $updated_at_attribute = 'updated_at';

	/**
	 * @inheritDoc
	 */
	public function events(){
		return [
			Model::EVENT_BEFORE_SAVE => 'saveTimestamp'
		];
	}

	/**
	 * @param \Drupal\singpost_base\ModelInterface $model
	 */
	public function saveTimestamp(ModelInterface $model){
		if ($model->is_new){
			$model->{$this->created_at_attribute} = Drupal::time()->getRequestTime();
		}else{
			$model->{$this->updated_at_attribute} = Drupal::time()->getRequestTime();
		}
	}
}