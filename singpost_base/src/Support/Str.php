<?php

namespace Drupal\singpost_base\Support;

/**
 * Class Str
 *
 * @package Drupal\singpost_base\Support
 */
class Str{

	/**
	 * The cache of snake-cased words.
	 *
	 * @var array
	 */
	protected static $snake_cache = [];

	/**
	 * Convert a string to snake case.
	 *
	 * @param string $value
	 * @param string $delimiter
	 *
	 * @return string
	 */
	public static function snake($value, $delimiter = '_'){
		$key = $value;

		if (isset(static::$snake_cache[$key][$delimiter])){
			return static::$snake_cache[$key][$delimiter];
		}

		if (!ctype_lower($value)){
			$value = preg_replace('/\s+/u', '', ucwords($value));

			$value = static::lower(preg_replace('/(.)(?=[A-Z])/u', '$1' . $delimiter, $value));
		}

		return static::$snake_cache[$key][$delimiter] = $value;
	}

	/**
	 * Convert the given string to lower-case.
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	public static function lower($value){
		return mb_strtolower($value, 'UTF-8');
	}
}