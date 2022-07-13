<?php

namespace Drupal\singpost_audit_trail\Form;

use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Url;
use Drupal\singpost_audit_trail\Repositories\AuditTrailRepository;
use Drupal\singpost_base\Support\FormHelper;

/**
 * Class AuditTrailTableForm
 *
 * @package Drupal\singpost_audit_trail\Form
 */
class AuditTrailTableForm extends FormBase{

	use MessengerTrait;

	/**
	 * @var AuditTrailRepository
	 */
	protected $_audit_trail;

	/**
	 * @var \Drupal\Core\Datetime\DateFormatter
	 */
	protected $_dateformat;

	/**
	 * @var array
	 */
	protected $_filters;

	/**
	 * AuditTrailTableForm constructor.
	 *
	 * @param AuditTrailRepository $audit_trail
	 * @param DateFormatter $dateformat
	 * @param array $filters
	 */
	public function __construct(
		AuditTrailRepository $audit_trail,
		DateFormatter $dateformat,
		array $filters){
		$this->_audit_trail = $audit_trail;
		$this->_dateformat  = $dateformat;
		$this->_filters     = $filters;
	}

	/**
	 * @inheritDoc
	 */
	public function buildForm(array $form, FormStateInterface $form_state){
		$limit = FormHelper::getPaginationLimitFromRequest(30);

		$header = [
			['data' => t('Action'), 'field' => 'action'],
			['data' => t('Type'), 'field' => 'type'],
			['data' => t('Date'), 'field' => 'created_at', 'sort' => 'desc'],
			['data' => t('Link'), 'field' => 'link'],
			['data' => t('Created By'), 'field' => 'created_by'],
			'actions' => t('Operations')
		];

		$pager = $this->_audit_trail->applyFilters($this->_filters)
		                            ->getTablePaginatedData($header, $limit);

		$logs = $this->_audit_trail->getTableData($pager);

		$form['form'] = [
			'#type'       => 'container',
			'#attributes' => ['class' => ['form--inline clearfix']]
		];

		$form['form']['pagination'] = FormHelper::createPaginationLimit('singpost.audit_trail.admin',
			$limit);

		$form['table'] = [
			'#type'       => 'table',
			'#header'     => $header,
			'#attributes' => [
				'id' => $this->getFormId()
			]
		];

		if (!empty($logs)){
			foreach ($logs as $log){
				$form['table'][$log->id] = [
					'action'     => [
						'#plain_text' => $log->action
					],
					'title'      => [
						'#plain_text' => $log->type
					],
					'created_at' => [
						'#plain_text' => $this->_dateformat->format($log->created_at)
					],
					'link'       => [
						'#plain_text' => $log->link
					],
					'created_by' => [
						'#plain_text' => $log->getAuthor()
					],
					'actions'    => [
						'#type'  => 'dropbutton',
						'#links' => [
							'view' => [
								'title' => t('View'),
								'url'   => Url::fromRoute('singpost.audit_trail.view', [
									'id' => $log->id
								])
							]
						],
					]
				];
			}

			$form['count'] = $pager->renderSummary();
		}else{
			$form['table']['#empty'] = t('No audit log found.');
		}

		return $form;
	}

	/**
	 * @inheritDoc
	 */
	public function getFormId(){
		return 'audit_trail_table_form';
	}

	/**
	 * @inheritDoc
	 */
	public function submitForm(array &$form, FormStateInterface $form_state){ }
}