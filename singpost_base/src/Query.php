<?php

namespace Drupal\singpost_base;

use Drupal\Core\Database\Query\PagerSelectExtender;
use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\Database\Query\TableSortExtender;
use Drupal\singpost_base\Support\Paginator;
use PDO;

/**
 * Class Query
 *
 * @package Drupal\singpost_base
 */
class Query implements QueryInterface{

	const ORDER_ASC = 'ASC';

	const ORDER_DESC = 'DESC';

	/**
	 * Database connection
	 *
	 * @var \Drupal\Core\Database\Connection
	 */
	protected $_connection;

	/**
	 * Query instance
	 *
	 * @var SelectInterface|PagerSelectExtender|TableSortExtender
	 */
	protected $_query;

	/**
	 * Model instance
	 *
	 * @var \Drupal\singpost_base\ModelInterface
	 */
	protected $_model;

	/**
	 * Flag result set as an array
	 *
	 * @var bool
	 */
	protected $_as_array = FALSE;

	/**
	 * Query constructor.
	 *
	 * @param \Drupal\singpost_base\ModelInterface $model
	 */
	public function __construct(ModelInterface $model){
		$this->_model      = $model;
		$this->_connection = $model->getConnection();

		$this->_query = $this->_connection->select($this->_model::tableName(),
			$this->_model::tableAlias());
	}

	/**
	 * @inheritDoc
	 */
	public function select(array $columns = []){
		$this->_query->fields($this->_model::tableAlias(), $columns);

		return $this;
	}

	/**
	 * @return array|\Drupal\singpost_base\Model[]
	 */
	public function all(){
		if ($this->_as_array){
			return $this->_query->execute()->fetchAll(PDO::FETCH_ASSOC);
		}

		return $this->_query->execute()
		                    ->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE,
			                    get_class($this->_model), ['is_new' => FALSE]);
	}

	/**
	 * @inheritDoc
	 */
	public function column(){
		return $this->_query->execute()->fetchCol();
	}

	/**
	 * @inheritDoc
	 */
	public function asArray(){
		$this->_as_array = TRUE;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function limit(int $limit = 15){
		$this->_query->range(0, $limit);

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function paginate(int $limit = 15){
		$this->_query = $this->_query->extend(PagerSelectExtender::class);
		$statement    = $this->_query->limit($limit)->execute();
		$items        = [];

		if (!empty($statement)){
			$items = $statement->fetchAll(PDO::FETCH_CLASS, get_class($this->_model),
				['is_new' => FALSE]);
		}

		return new Paginator($limit, $this->count(), 1, $items);
	}

	/**
	 * @inheritDoc
	 */
	public function count(){
		return intval($this->_query->countQuery()->execute()->fetchField());
	}

	/**
	 * @inheritDoc
	 */
	public function condition(array $conditions){
		if (is_string($conditions[0])){
			if (empty($conditions[2])){
				$operator = '=';
			}else{
				$operator = $conditions[2];
			}

			$this->_query->condition($conditions[0], $conditions[1], $operator);
		}else{
			foreach ($conditions as $condition){
				if (empty($condition[2])){
					$operator = '=';
				}else{
					$operator = $condition[2];
				}

				$this->_query->condition($condition[0], $condition[1], $operator);
			}
		}

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function where(string $snippet, array $args = []){
		$this->_query->where($snippet, $args);

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function orderBy($order = NULL, $dir = self::ORDER_ASC){
		if (!$order){
			$order = $this->_model::tablePrimaryKey();
		}

		$this->_query->orderBy($order, $dir);

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function groupBy(string $field){
		$this->_query->groupBy($field);

		return $this;
	}

	/**
	 * @return \Drupal\singpost_base\Model|false
	 */
	public function one(){
		$statement = $this->_query->execute();
		$statement->setFetchMode(PDO::FETCH_CLASS, get_class($this->_model), ['is_new' => FALSE]);

		return $statement->fetch();
	}

	/**
	 * @param array $header
	 *
	 * @return $this
	 */
	public function sortByHeader(array $header){
		$this->_query = $this->_query->extend(TableSortExtender::class);
		$this->_query->orderByHeader($header);

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function toSql(){
		return $this->_query->__toString();
	}

	/**
	 * @inheritDoc
	 */
	public function getQuery(){
		return $this->_query;
	}


	/**
	 * @param int $key_index
	 * @param int $value_index
	 *
	 * @return mixed
	 */
	public function fetchAllKeyed($key_index = 0, $value_index = 1){
		return $this->_query->execute()->fetchAllKeyed($key_index, $value_index);
	}
}