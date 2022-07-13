<?php

namespace Drupal\singpost_base;

/**
 * Interface QueryInterface
 *
 * @package Drupal\singpost_base
 */
interface QueryInterface{

	/**
	 * Add columns
	 *
	 * @param array $columns
	 *
	 * @return $this
	 */
	public function select(array $columns = []);

	/**
	 * Get all of the models from the database.
	 *
	 * @return array|\Drupal\singpost_base\Model[]
	 */
	public function all();

	/**
	 * Get single column
	 *
	 * @return $this
	 */
	public function column();

	/**
	 * Flag result as an array
	 *
	 * @return $this
	 */
	public function asArray();

	/**
	 * Limit results
	 *
	 * @param int $limit
	 *
	 * @return $this
	 */
	public function limit(int $limit = 15);

	/**
	 * Paginate result
	 *
	 * @param int $limit
	 *
	 * @return \Drupal\singpost_base\Support\Paginator
	 */
	public function paginate(int $limit = 15);

	/**
	 * Filter model by array of conditions.
	 *
	 * @param array $conditions
	 *
	 * @return $this
	 */
	public function condition(array $conditions);

	/**
	 * Adds an arbitrary WHERE clause to the query.
	 *
	 * @param string $snippet
	 *   A portion of a WHERE clause as a prepared statement. It must use named
	 *   placeholders, not ? placeholders. The caller is responsible for providing
	 *   unique placeholders that do not interfere with the placeholders generated
	 *   by this QueryConditionInterface object.
	 * @param array $args
	 *   An associative array of arguments keyed by the named placeholders.
	 *
	 * @return $this
	 */
	public function where(string $snippet, array $args);

	/**
	 * Order model
	 *
	 * @param $order
	 * @param $dir
	 *
	 * @return $this
	 */
	public function orderBy($order, $dir);

	/**
	 * @param string $field
	 *
	 * @return $this
	 */
	public function groupBy(string $field);

	/**
	 * Count query
	 *
	 * @return int
	 */
	public function count();

	/**
	 * Fetch single record
	 *
	 * @return \Drupal\singpost_base\Model
	 */
	public function one();

	/**
	 * @param array $header
	 *
	 * @return $this
	 */
	public function sortByHeader(array $header);

	/**
	 * Get model query
	 *
	 * @return \Drupal\Core\Database\Query\SelectInterface
	 */
	public function getQuery();

	/**
	 * The Select Query object expressed as a string.
	 *
	 * @return string
	 */
	public function toSql();

	/**
	 * Get model fetchAllKeyed
	 *
	 * @return \Drupal\Core\Database\Query\SelectInterface
	 */
	public function fetchAllKeyed();
}