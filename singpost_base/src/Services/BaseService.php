<?php

namespace Drupal\singpost_base\Services;

use Drupal\singpost_base\ModelInterface;
use Drupal\singpost_base\Repositories\BaseRepository;
use Drupal\singpost_base\Support\Paginator;

/**
 * Class BaseService
 *
 * @package Drupal\singpost_base\Services
 */
abstract class BaseService implements BaseRepository{

	/**
	 * @var \Drupal\singpost_base\ModelInterface
	 */
	public $model;

	/**
	 * @var array
	 */
	protected $_filters;

	/**
	 * BaseService constructor.
	 *
	 * @param ModelInterface $model
	 */
	public function __construct(ModelInterface $model){
		$this->model = $model;
	}

	/**
	 * @inheritDoc
	 */
	public function getModel(){
		return $this->model;
	}

	/**
	 * @inheritDoc
	 */
	public function getTablePaginatedData(array $header, int $limit = 15){
		$query = $this->model::find();

		if (!empty($this->_filters) && is_array($this->_filters) && !empty($this->_filters['where'])){
			$query->where($this->_filters['where'], $this->_filters['args']);
		}

		return $query->sortByHeader($header)->paginate($limit);
	}

	/**
	 * @inheritDoc
	 */
	public function getTableData(Paginator $paginator){
		return $paginator->getItems();
	}

	/**
	 * @inheritDoc
	 */
	public function applyFilters(array $params){
		$this->_filters = $params;

		return $this;
	}
}