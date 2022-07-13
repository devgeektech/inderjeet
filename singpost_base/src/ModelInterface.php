<?php

namespace Drupal\singpost_base;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException as NotFoundHttpExceptionAlias;

/**
 * Interface ModelInterface
 *
 * @package Drupal\singpost_base
 */
interface ModelInterface{

	/**
	 * Get the table associated with the model.
	 *
	 * @return string
	 */
	public static function tableName();

	/**
	 * Get the table alias associated with the model
	 *
	 * @return string
	 */
	public static function tableAlias();

	/**
	 * Get the table primary field name
	 *
	 * @return string
	 */
	public static function tablePrimaryKey();

	/**
	 * Create an instance of model
	 *
	 * @return \Drupal\singpost_base\QueryInterface
	 */
	public static function find();

	/**
	 * @param mixed $conditions
	 *
	 * @return static|false
	 */
	public static function findOne($conditions);

	/**
	 * @param mixed $conditions
	 *
	 * @return static|NotFoundHttpExceptionAlias
	 * @throws NotFoundHttpExceptionAlias
	 */
	public static function findOrFail($conditions);

	/**
	 * @param mixed $conditions
	 *
	 * @return static[]
	 */
	public static function findAll($conditions);

	/**
	 * @return \Drupal\Core\Database\Connection
	 */
	public function getConnection();

	/**
	 * Check if model exists
	 *
	 * @return boolean
	 */
	public function exists();

	/**
	 * Load data array to model object
	 *
	 * @param array $data
	 *
	 * @return static
	 */
	public function load(array $data);

	/**
	 * Save model
	 *
	 * @return boolean
	 */
	public function save();

	/**
	 * @param array $attributes
	 *
	 * @return boolean
	 */
	public function updateAttributes(array $attributes);

	/**
	 * Delete model item
	 *
	 * @return boolean
	 */
	public function delete();
}