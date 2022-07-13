<?php

namespace Drupal\singpost_audit_trail\Services;

use Drupal;
use Drupal\singpost_audit_trail\Model\AuditTrail;
use Drupal\singpost_audit_trail\Repositories\AuditTrailRepository;
use Drupal\singpost_base\Services\BaseService;

/**
 * Class AuditTrailService
 *
 * @package Drupal\singpost_audit_trail\Services
 */
class AuditTrailService extends BaseService implements AuditTrailRepository{

	/**
	 * @inheritDoc
	 */
	public function log(
		$action,
		$request,
		$response,
		$link,
		$requested_at,
		$responded_at,
		$type = AuditTrail::TYPE_SUCCESS){
		$log = $this->model->load([
			'action'       => $action,
			'request'      => $request,
			'response'     => $response,
			'link'         => $link,
			'type'         => $type,
			'created_at'   => Drupal::time()->getRequestTime(),
			'created_by'   => Drupal::currentUser()->id() ?? NULL,
			'requested_at' => $requested_at,
			'responded_at' => $responded_at
		]);

		return $log->save();
	}

	/**
	 * @return array
	 */
	public function getActions(){
		$actions = $this->model::find(['action'])->column();

		return $actions ? array_combine($actions, $actions) : [];
	}

	/**
	 * Delete audit logs cron
	 */
	public function deleteAuditTrail(){
		$days       = Drupal::config('singpost.audit_trail')->get('delete_interval') ?? 180;
		$days       = $days * 24 * 60 * 60;
		$connection = Drupal::database();

		$rows = $connection->select('audit_trail', 'sat')
		                   ->fields('sat', ['id'])
		                   ->condition('created_at',
			                   Drupal::time()->getRequestTime() - $days, '<')
		                   ->range(0, 1000)
		                   ->execute()
		                   ->fetchCol();

		if ($rows){
			$connection->delete('audit_trail')
			           ->condition('id', $rows, 'IN')
			           ->execute();
		}
	}
}