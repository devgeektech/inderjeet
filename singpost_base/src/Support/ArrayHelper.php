<?php

namespace Drupal\singpost_base\Support;

use Closure;

/**
 * Yii2 ArrayHelper
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ArrayHelper{

	/**
	 * Returns the values of a specified column in an array.
	 * The input array should be multidimensional or an array of objects.
	 *
	 * For example,
	 *
	 * ```php
	 * $array = [
	 *     ['id' => '123', 'data' => 'abc'],
	 *     ['id' => '345', 'data' => 'def'],
	 * ];
	 * $result = ArrayHelper::getColumn($array, 'id');
	 * // the result is: ['123', '345']
	 *
	 * // using anonymous function
	 * $result = ArrayHelper::getColumn($array, function ($element) {
	 *     return $element['id'];
	 * });
	 * ```
	 *
	 * @param array $array
	 * @param int|string|\Closure $name
	 * @param bool $keepKeys whether to maintain the array keys. If false, the resulting array
	 * will be re-indexed with integers.
	 *
	 * @return array the list of column values
	 */
	public static function getColumn($array, $name, $keepKeys = TRUE){
		$result = [];
		if ($keepKeys){
			foreach ($array as $k => $element){
				$result[$k] = static::getValue($element, $name);
			}
		}else{
			foreach ($array as $element){
				$result[] = static::getValue($element, $name);
			}
		}

		return $result;
	}

	/**
	 * Retrieves the value of an array element or object property with the given key or property
	 * name. If the key does not exist in the array, the default value will be returned instead.
	 * Not used when getting value from an object.
	 *
	 * The key may be specified in a dot format to retrieve the value of a sub-array or the
	 * property
	 * of an embedded object. In particular, if the key is `x.y.z`, then the returned value would
	 * be `$array['x']['y']['z']` or `$array->x->y->z` (if `$array` is an object). If `$array['x']`
	 * or `$array->x` is neither an array nor an object, the default value will be returned.
	 * Note that if the array already has an element `x.y.z`, then its value will be returned
	 * instead of going through the sub-arrays. So it is better to be done specifying an array of
	 * key names like `['x', 'y', 'z']`.
	 *
	 * Below are some usage examples,
	 *
	 * ```php
	 * // working with array
	 * $username = \yii\helpers\ArrayHelper::getValue($_POST, 'username');
	 * // working with object
	 * $username = \yii\helpers\ArrayHelper::getValue($user, 'username');
	 * // working with anonymous function
	 * $fullName = \yii\helpers\ArrayHelper::getValue($user, function ($user, $defaultValue) {
	 *     return $user->firstName . ' ' . $user->lastName;
	 * });
	 * // using dot format to retrieve the property of embedded object
	 * $street = \yii\helpers\ArrayHelper::getValue($users, 'address.street');
	 * // using an array of keys to retrieve the value
	 * $value = \yii\helpers\ArrayHelper::getValue($versions, ['1.0', 'date']);
	 * ```
	 *
	 * @param array|object $array array or object to extract value from
	 * @param string|\Closure|array $key key name of the array element, an array of keys or
	 *     property name of the object, or an anonymous function returning the value. The anonymous
	 *     function signature should be:
	 * `function($array, $defaultValue)`.
	 * The possibility to pass an array of keys is available since version 2.0.4.
	 * @param mixed $default the default value to be returned if the specified array key does not
	 *     exist. Not used when getting value from an object.
	 *
	 * @return mixed the value of the element if found, default value otherwise
	 */
	public static function getValue($array, $key, $default = NULL){
		if ($key instanceof Closure){
			return $key($array, $default);
		}

		if (is_array($key)){
			$last_key = array_pop($key);
			foreach ($key as $key_part){
				$array = static::getValue($array, $key_part);
			}
			$key = $last_key;
		}

		if (is_array($array) && (isset($array[$key]) || array_key_exists($key, $array))){
			return $array[$key];
		}

		if (($pos = strrpos($key, '.')) !== FALSE){
			$array = static::getValue($array, substr($key, 0, $pos), $default);
			$key   = substr($key, $pos + 1);
		}

		if (is_object($array)){
			// this is expected to fail if the property does not exist, or __get() is not implemented
			// it is not reliably possible to check whether a property is accessible beforehand
			return $array->$key;
		}elseif (is_array($array)){
			return (isset($array[$key]) || array_key_exists($key,
					$array)) ? $array[$key] : $default;
		}

		return $default;
	}

	/**
	 * Builds a map (key-value pairs) from a multidimensional array or an array of objects.
	 * The `$from` and `$to` parameters specify the key names or property names to set up the map.
	 * Optionally, one can further group the map according to a grouping field `$group`.
	 *
	 * For example,
	 *
	 * ```php
	 * $array = [
	 *     ['id' => '123', 'name' => 'aaa', 'class' => 'x'],
	 *     ['id' => '124', 'name' => 'bbb', 'class' => 'x'],
	 *     ['id' => '345', 'name' => 'ccc', 'class' => 'y'],
	 * ];
	 *
	 * $result = ArrayHelper::map($array, 'id', 'name');
	 * // the result is:
	 * // [
	 * //     '123' => 'aaa',
	 * //     '124' => 'bbb',
	 * //     '345' => 'ccc',
	 * // ]
	 *
	 * $result = ArrayHelper::map($array, 'id', 'name', 'class');
	 * // the result is:
	 * // [
	 * //     'x' => [
	 * //         '123' => 'aaa',
	 * //         '124' => 'bbb',
	 * //     ],
	 * //     'y' => [
	 * //         '345' => 'ccc',
	 * //     ],
	 * // ]
	 * ```
	 *
	 * @param array $array
	 * @param string|\Closure $from
	 * @param string|\Closure $to
	 * @param string|\Closure $group
	 *
	 * @return array
	 */
	public static function map($array, $from, $to, $group = NULL){
		$result = [];
		foreach ($array as $element){
			$key   = static::getValue($element, $from);
			$value = static::getValue($element, $to);
			if ($group !== NULL){
				$result[static::getValue($element, $group)][$key] = $value;
			}else{
				$result[$key] = $value;
			}
		}

		return $result;
	}
}