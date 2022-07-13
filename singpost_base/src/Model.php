<?php


namespace Drupal\singpost_base;


use Drupal;
use Drupal\Core\Database\Database;
use Drupal\singpost_base\Support\Str;
use Exception;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class Model
 *
 * @package Drupal\singpost_base
 */
abstract class Model implements ModelInterface{

	const EVENT_BEFORE_SAVE = 'beforeSave';

	const EVENT_AFTER_SAVE = 'afterSave';

	const EVENT_AFTER_DELETE = 'afterDelete';

	/**
	 * @var array
	 */
	public static $columns = [];
	/**
	 * @var boolean
	 */
	public $is_new;
	/**
	 * List of behaviors
	 *
	 * @var array
	 */
	protected $_behaviors = [];
	/**
	 * The model's attributes
	 *
	 * @var array
	 */
	protected $_attributes = [];
	/**
	 * The model's old attributes
	 *
	 * @var array
	 */
	protected $_old_attributes = [];

	/**
	 * Model constructor.
	 *
	 * @param bool $is_new
	 */
	public function __construct(bool $is_new = TRUE){
		$this->is_new = $is_new;

		$this->_initBehavior();
		$this->afterFind();
	}

	/**
	 * Create events behaviors
	 */
	private function _initBehavior(){
		if (!empty($this->behaviors())){
			$behaviors = [];
			$events    = [];

			foreach ($this->behaviors() as $name => $behavior){
				if ($behavior['class']){
					$behaviors[$name] = new $behavior['class'];

					if ($behaviors[$name] instanceof Behavior){
						unset($behavior['class']);

						foreach ($behavior as $property => $value){
							if (property_exists($behaviors[$name], $property)){
								$behaviors[$name]->$property = $value;
							}
						}

						foreach ($behaviors[$name]->events() as $event => $method){
							$events[$event][$name]['class']  = $behaviors[$name];
							$events[$event][$name]['method'] = $method;
						}
					}
				}
			}

			$this->_behaviors = $events;
		}
	}

	/**
	 * @inherit
	 */
	public function behaviors(){
		return [];
	}

	/**
	 * Run after Model instantiated
	 */
	public function afterFind(){

	}

	/**
	 * @inheritDoc
	 */
	public static function tableAlias(){
		return static::tableName();
	}

	/**
	 * @inheritDoc
	 */
	public static function tableName(){
		return Str::snake(class_basename(get_called_class()));
	}

	/**
	 * @param mixed $conditions
	 *
	 * @return NotFoundHttpException|static
	 */
	public static function findOrFail($conditions){
		$model = static::findOne($conditions);

		if (!$model){
			throw new NotFoundHttpException(t('The requested page does not exist.'));
		}

		return $model;
	}

	/**
	 * @param mixed $conditions
	 *
	 * @return static|false
	 */
	public static function findOne($conditions){
		return static::_findByCondition($conditions)->one();
	}

	/**
	 * Find model by array of conditions. If string provided, the model will be filtered by
	 * primary key.
	 *
	 * @param mixed $conditions
	 *
	 * @return \Drupal\singpost_base\QueryInterface
	 */
	protected static function _findByCondition($conditions){
		$query = static::find(static::$columns);

		if (!is_null($conditions)){
			if (is_array($conditions)){
				$query->condition($conditions);
			}else{
				$query->condition([static::tablePrimaryKey(), $conditions, '=']);
			}
		}

		return $query;
	}

	/**
	 * @inheritDoc
	 */
	public static function find(array $columns = []){
		static::$columns = $columns;

		return (new Query(new static))->select($columns);
	}

	/**
	 * @inheritDoc
	 */
	public static function tablePrimaryKey(){
		return 'id';
	}

	/**
	 * @param mixed $conditions
	 *
	 * @return static[]
	 */
	public static function findAll($conditions){
		return static::_findByCondition($conditions)->all();
	}

	/**
	 * @inheritDoc
	 */
	public function exists(){
		if ($this->{static::tablePrimaryKey()} === NULL){
			return FALSE;
		}

		return static::findOne($this->{static::tablePrimaryKey()});
	}

	/**
	 * @inheritDoc
	 */
	public function load(array $data){
		foreach ($data as $attribute => $value){
			$this->$attribute = $value;
		}

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function save(){
		static::beforeSave();

		if ($this->is_new){
			return $this->_create();
		}

		return $this->_update();
	}

	/**
	 * Called before model save
	 */
	public function beforeSave(){
		if (!$this->is_new){
			$old_attributes = [];

			if (!empty($this->_attributes)){
				foreach ($this->_attributes as $attribute){
					$old_attributes[$attribute] = $this->$attribute;
				}

				$this->_old_attributes = $old_attributes;
			}
		}

		$this->_runBehaviorsByEvent(self::EVENT_BEFORE_SAVE);

		$attributes = [];

		if (!empty($this->_attributes)){
			foreach ($this->_attributes as $attribute){
				if (property_exists($this, $attribute)){
					$attributes[$attribute] = $this->$attribute;
				}
			}

			$this->_attributes = $attributes;
		}
	}

	/**
	 * @param string $event
	 */
	private function _runBehaviorsByEvent($event){
		if (!empty($this->_behaviors[$event])){
			foreach ($this->_behaviors[$event] as $behavior){
				$behavior['class']->{$behavior['method']}($this);
			}
		}
	}

	/**
	 * @return bool
	 */
	protected function _create(){
		try{
			$last_insert_id = $this->getConnection()->insert(static::tableName())
			                       ->fields($this->_attributes)
			                       ->execute();

			static::afterSave();

			//Get latest id
			$this->id = $last_insert_id;

			return TRUE;
		}catch (Exception $e){
			Drupal::logger(static::tableName())->error($e->getMessage());

			return FALSE;
		}
	}

	/**
	 * @inheritDoc
	 */
	public function getConnection(){
		return Database::getConnection();
	}

	/**
	 * Called after model save
	 */
	public function afterSave(){
		$this->_runBehaviorsByEvent(self::EVENT_AFTER_SAVE);
	}

	/**
	 * @return bool
	 */
	protected function _update(){
		try{
			$this->getConnection()->update(static::tableName())
			     ->fields($this->_attributes)
			     ->condition(static::tablePrimaryKey(),
				     $this->{static::tablePrimaryKey()})
			     ->execute();

			static::afterSave();

			return TRUE;
		}catch (Exception $e){
			Drupal::logger(static::tableName())->error($e->getMessage());

			return FALSE;
		}
	}

	/**
	 * @inheritDoc
	 */
	public function delete(){
		try{
			$this->getConnection()->delete(static::tableName())
			     ->condition(static::tablePrimaryKey(),
				     $this->{static::tablePrimaryKey()})
			     ->execute();

			static::afterDelete();

			return TRUE;
		}catch (Exception $e){
			Drupal::logger(static::tableName())->error($e->getMessage());

			return FALSE;
		}
	}

	/**
	 * Called after model delete
	 */
	public function afterDelete(){
		$this->_runBehaviorsByEvent(self::EVENT_AFTER_DELETE);
	}

	/**
	 * @inheritDoc
	 */
	public function updateAttributes(array $attributes){
		$attrs = [];
		foreach ($attributes as $name => $value){
			if (in_array($name, $this->_attributes)){
				$attrs[$name] = $value;
			}
		}

		if ($this->is_new){
			return FALSE;
		}

		try{
			$this->getConnection()->update(static::tableName())
			     ->fields($attrs)
			     ->condition(static::tablePrimaryKey(),
				     $this->{static::tablePrimaryKey()})
			     ->execute();

			return TRUE;
		}catch (Exception $e){
			Drupal::logger(static::tableName())->error($e->getMessage());

			return FALSE;
		}
	}
}