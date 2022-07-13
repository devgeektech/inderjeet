<?php

namespace Drupal\singpost_base;

/**
 * Class Behavior
 *
 * @package Drupal\singpost_base
 */
abstract class Behavior{

	/**
	 * @return string[]
	 */
	abstract public function events();
}