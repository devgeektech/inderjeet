<?php

namespace Drupal\singpost_audit_trail\Form;

use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Form\FormStateInterface;
use Drupal\singpost_audit_trail\Model\AuditTrail;
use Drupal\singpost_audit_trail\Repositories\AuditTrailRepository;
use Drupal\singpost_base\Form\BaseSearchForm;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AuditTrailSearchForm
 *
 * @package Drupal\singpost_audit_trail\Form
 */
class AuditTrailSearchForm extends BaseSearchForm{

	/**
	 * @var AuditTrailRepository
	 */
	protected $_audit_trail;

	/**
	 * @var DateFormatter
	 */
	protected $_dateformat;

	/**
	 * AuditTrailSearchForm constructor.
	 *
	 * @param Request $request
	 * @param DateFormatter $dateformat
	 * @param AuditTrailRepository $audit_trail
	 */
	public function __construct(
		Request $request,
		DateFormatter $dateformat,
		AuditTrailRepository $audit_trail){
		parent::__construct($request);

		$this->_audit_trail = $audit_trail;
		$this->_dateformat  = $dateformat;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFormId(){
		return 'audit_trail_search_form';
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildForm(array $form, FormStateInterface $form_state){
		$form['#attached']['library'][] = 'singpost_base/datetimepicker';

		$form['filters'] = [
			'#type'       => 'container',
			'#title'      => t('Search Audit Trail'),
			'#attributes' => ['class' => ['form--inline clearfix']],
		];

		return parent::buildForm($form, $form_state);
	}

	/**
	 * @inheritDoc
	 */
	public function searchFilters($default_values = []){
		$filters['action'] = [
			'#type'          => 'select',
			'#title'         => t('Action'),
			'#placeholder'   => t('Search by Action'),
			'#default_value' => !empty($default_values['action']) ? $default_values['action'] : '',
			'#empty_option'  => t('All'),
			'#options'       => $this->_audit_trail->getActions(),
			'field'          => 'action',
			'condition'      => '=',
		];

		$filters['start_date'] = [
			'#type'          => 'textfield',
			'#title'         => t('Start Date'),
			'#attributes'    => [
				'class'    => ['calendar start-date'],
				'readonly' => TRUE
			],
			'#default_value' => !empty($default_values['start_date']) ? $this->_dateformat->format(
				$default_values['start_date'], 'custom', 'd-m-Y G:i') : '',
			'#size'          => 30,
			'#placeholder'   => t('From date'),
			'field'          => 'created_at',
			'condition'      => '>=',
		];

		$filters['end_date'] = [
			'#type'          => 'textfield',
			'#title'         => t('End Date'),
			'#attributes'    => [
				'class'    => ['calendar end-date'],
				'readonly' => TRUE
			],
			'#default_value' => !empty($default_values['end_date']) ? $this->_dateformat->format(
				$default_values['end_date'], 'custom', 'd-m-Y G:i') : '',
			'#size'          => 30,
			'#placeholder'   => t('To date'),
			'field'          => 'created_at',
			'condition'      => '<='
		];

		$filters['type'] = [
			'#type'          => 'select',
			'#title'         => t('Type'),
			'#attributes'    => [
				'class' => ['select2']
			],
			'#default_value' => $default_values['type'] ?? '',
			'#options'       => [
				''                       => t('All'),
				AuditTrail::TYPE_SUCCESS => t('Success'),
				AuditTrail::TYPE_FAILED  => t('Failed')
			],
			'field'          => 'type',
			'condition'      => '='
		];

		return $filters;
	}

	/**
	 * @inheritDoc
	 */
	protected function _processFormValues(array $values){
		$filters = [];
		foreach ($values as $field => $value){
			if ($value !== NULL && $value !== ''){
				if ($field == 'start_date' || $field == 'end_date'){
					$value = strtotime(trim($value));
				}

				$filters[$field] = $value;
			}
		}

		return $filters;
	}
}