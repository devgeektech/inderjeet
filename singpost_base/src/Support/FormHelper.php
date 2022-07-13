<?php

namespace Drupal\singpost_base\Support;

use Drupal;
use Drupal\Core\Url;

/**
 * Class FormHelper
 *
 * @package Drupal\singpost_base\Support
 */
class FormHelper{

	/**
	 * @param int $default
	 *
	 * @return int|string
	 */
	public static function getPaginationLimitFromRequest(int $default = 15){
		$limit = Drupal::request()->get('limit', $default);

		if (!is_numeric($limit)){
			return $default;
		}

		return $limit;
	}

	/**
	 * Create a pagination limit dropdown
	 *
	 * @param $route
	 * @param int $limit
	 *
	 * @return array
	 */
	public static function createPaginationLimit($route, $limit = 30){
		return [
			'#type'          => 'select',
			'#title'         => t('Display Limit'),
			'#default_value' => $limit,
			'#attributes'    => [
				'class'    => ['pagination-limit'],
				'data-url' => Url::fromRoute($route,
					Drupal::request()->query->all())->setAbsolute()->toString()
			],
			'#options'       => [
				15  => t('15 Items'),
				30  => t('30 Items'),
				60  => t('60 Items'),
				100 => t('100 Items')
			],
		];
	}
}