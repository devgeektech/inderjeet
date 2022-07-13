<?php


namespace Drupal\singpost_base\Behaviors;


use Drupal;
use Drupal\singpost_base\Behavior;
use Drupal\singpost_base\Model;
use Drupal\singpost_base\ModelInterface;

/**
 * Class BlameableBehavior
 *
 * @package Drupal\singpost_base\Behaviors
 */
class BlameableBehavior extends Behavior{

	/**
	 * @var string
	 */
	public $created_by_attribute = 'created_by';

	/**
	 * @var string
	 */
	public $updated_by_attribute = 'updated_by';

	/**
	 * @inheritDoc
	 */
	public function events(){
		return [
			Model::EVENT_BEFORE_SAVE => 'saveUserstamp'
		];
	}

	/**
	 * @param \Drupal\singpost_base\ModelInterface $model
	 */
	public function saveUserstamp(ModelInterface $model){
		if ($model->is_new){
			$model->{$this->created_by_attribute} = Drupal::currentUser()->id();
		}else{
			$model->{$this->updated_by_attribute} = Drupal::currentUser()->id();
		}
	}
}