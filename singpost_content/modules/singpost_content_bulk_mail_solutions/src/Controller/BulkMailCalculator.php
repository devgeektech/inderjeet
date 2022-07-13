<?php

namespace Drupal\singpost_content_bulk_mail_solutions\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\singpost_content_bulk_mail_solutions\Helper\Calculator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class BulkMailCalculator
 *
 * @package Drupal\singpost_content_bulk_mail_solutions\Controller
 */
class BulkMailCalculator extends ControllerBase{

	/**
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 *
	 * @return \Drupal\Core\Ajax\AjaxResponse
	 */
	public function calculate(Request $request){
		$calculator = new Calculator($request);
		$result     = $calculator->calculate();

		return new JsonResponse(isset($result['#theme']) ? render($result) : $result, 200);
	}
}