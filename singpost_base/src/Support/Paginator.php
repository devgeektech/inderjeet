<?php

namespace Drupal\singpost_base\Support;

use Drupal;

/**
 * Class Paginator
 *
 * @package Drupal\singpost_base\Support
 */
class Paginator{

	/**
	 * @var \Drupal\Core\Pager\Pager
	 */
	protected $_pager;

	/**
	 * @var \Drupal\Core\Pager\PagerManagerInterface
	 */
	protected $_pager_manager;

	/**
	 * @var array
	 */
	protected $_items = [];

	/**
	 * Paginator constructor.
	 *
	 * @param int $limit
	 * @param int $total
	 * @param int $element
	 * @param array $items
	 */
	public function __construct(int $limit, int $total, int $element, array $items = []){
		$this->_items         = $items;
		$this->_pager_manager = Drupal::service('pager.manager');
		$this->_pager         = $this->_pager_manager->createPager($total, $limit, $element);
	}

	/**
	 * @return \Drupal\singpost_base\Model[]
	 */
	public function getItems(){
		return $this->_items;
	}

	/**
	 * @return int
	 */
	public function getTotalPages(){
		return $this->_pager->getTotalPages();
	}

	/**
	 * Table pagination summary text
	 *
	 * @param string $message
	 *
	 * @return array
	 */
	public function renderSummary(string $message = ''){
		$limit       = $this->getLimit();
		$current     = $this->getCurrentPage();
		$total_items = $this->getTotalItems();

		$start = $current * $limit + 1;
		$end   = $start + $limit - 1;
		$end   = ($end > $total_items) ? $total_items : $end;

		return [
			'#markup' => t($message ?: 'Showing @start-@end of @total items.', [
				'@start' => $start,
				'@end'   => $end,
				'@total' => $total_items
			]),
		];
	}

	/**
	 * @return int
	 */
	public function getLimit(){
		return $this->_pager->getLimit();
	}

	/**
	 * @return int
	 */
	public function getCurrentPage(){
		return $this->_pager->getCurrentPage();
	}

	/**
	 * @return int
	 */
	public function getTotalItems(){
		return $this->_pager->getTotalItems();
	}
}