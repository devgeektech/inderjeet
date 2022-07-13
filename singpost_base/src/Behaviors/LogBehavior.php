<?php

namespace Drupal\singpost_base\Behaviors;

use Drupal;
use Drupal\singpost_base\Behavior;
use Drupal\singpost_base\Model;
use Drupal\singpost_base\ModelInterface;

/**
 * Class LogBehavior
 *
 * @package Drupal\singpost_base\Behaviors
 */
class LogBehavior extends Behavior{

	/**
	 * @var string
	 */
	public $module;

	/**
	 * @var string
	 */
	public $attribute;

	/**
	 * @inheritDoc
	 */
	public function events(){
		return [
			Model::EVENT_AFTER_SAVE   => 'afterSave',
			Model::EVENT_AFTER_DELETE => 'afterDelete'
		];
	}

	/**
	 * @param \Drupal\singpost_base\ModelInterface $model
	 */
	public function afterSave(ModelInterface $model){
		$this->setMessage($model, $model->is_new ? 'Create' : 'Update');
	}

	/**
	 * @param \Drupal\singpost_base\ModelInterface $model
	 * @param $action
	 */
	private function setMessage(ModelInterface $model, $action){
		$type = class_basename($model);

		if (!$this->module){
			$this->module = strtolower($type);
		}

		if ($model->is_new){
			$message = t('[@action] @type: @attribute', [
				'@action'    => $action,
				'@type'      => $type,
				'@attribute' => $model->{$this->attribute}
			]);
		}else{
			$message = t('[@action] @type: @attribute (@primaryKey)', [
				'@action'     => $action,
				'@type'       => $type,
				'@attribute'  => $model->{$this->attribute},
				'@primaryKey' => $model->{$model::tablePrimaryKey()}
			]);
		}

		Drupal::logger($this->module)->info($message);
	}

	/**
	 * @param \Drupal\singpost_base\ModelInterface $model
	 */
	public function afterDelete(ModelInterface $model){
		$this->setMessage($model, 'Delete');
	}
}