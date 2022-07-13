<?php

namespace Drupal\singpost_audit_trail\Repositories;

use Drupal\singpost_audit_trail\Model\AuditTrail;
use Drupal\singpost_base\Repositories\BaseRepository;
use Drupal\singpost_base\Support\Paginator;

/**
 * Interface AuditTrailRepository
 *
 * @package Drupal\singpost_audit_trail\Repositories
 */
interface AuditTrailRepository extends BaseRepository{

	/**
	 * Log an action
	 *
	 * @param string $action
	 * @param string $request
	 * @param string $response
	 * @param string $link
	 * @param int $requested_at
	 * @param int $responded_at
	 * @param string $type
	 *
	 * @return mixed
	 */
	public function log(
		$action,
		$request,
		$response,
		$link,
		$requested_at,
		$responded_at,
		$type = AuditTrail::TYPE_SUCCESS);

	/**
	 * @param Paginator $paginator
	 *
	 * @return AuditTrail[]
	 */
	public function getTableData(Paginator $paginator);

	/**
	 * @return array
	 */
	public function getActions();

	/**
	 * @return void
	 */
	public function deleteAuditTrail();
}