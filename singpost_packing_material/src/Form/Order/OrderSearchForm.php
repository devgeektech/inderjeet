<?php


namespace Drupal\singpost_packing_material\Form\Order;


use Drupal;
use Drupal\Core\Form\FormStateInterface;
use Drupal\singpost_base\Form\BaseSearchForm;
use Drupal\singpost_packing_material\Repositories\OrderRepository;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class OrderSearchForm
 *
 * @package Drupal\singpost_packing_material\Form\Order
 */
class OrderSearchForm extends BaseSearchForm{

	/**
	 * @var \Drupal\singpost_packing_material\Repositories\OrderRepository
	 */
	protected $_service;

	/**
	 * OrderSearchForm constructor.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @param \Drupal\singpost_packing_material\Repositories\OrderRepository $service
	 */
	public function __construct(Request $request, OrderRepository $service){
		parent::__construct($request);
		$this->_service = $service;
	}

	/**
	 * @return string
	 */
	public function getFormId(){
		return 'pm_order_search';
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 *
	 * @return array
	 */
	public function buildForm(array $form, FormStateInterface $form_state){
		$form['#attached']['library'][] = 'singpost_base/datetimepicker';

		$form['filters'] = [
			'#type'       => 'container',
			'#title'      => t('Search Name'),
			'#attributes' => ['class' => ['form--inline clearfix']],
		];

		return parent::buildForm($form, $form_state);
	}

	/**
	 * @param array $values
	 *
	 * @return array|mixed
	 */
	public function searchFilters($values = []){
		$filters['name'] = [
			'#type'          => 'search',
			'#title'         => t('Name'),
			'#placeholder'   => t('Search by name'),
			'#default_value' => !empty($values['name']) ? $values['name'] : '',
			'#size'          => 25,
			'field'          => 'name',
			'condition'      => 'LIKE',
		];

		$filters['block_number'] = [
			'#type'          => 'search',
			'#title'         => t('Block Number'),
			'#placeholder'   => t('Search by block number'),
			'#default_value' => !empty($values['block_number']) ? $values['block_number'] : '',
			'#size'          => 25,
			'field'          => 'block_number',
			'condition'      => 'LIKE',
		];

		$filters['street_name'] = [
			'#type'          => 'search',
			'#title'         => t('Street Name'),
			'#placeholder'   => t('Search by street name'),
			'#default_value' => !empty($values['street_name']) ? $values['street_name'] : '',
			'#size'          => 25,
			'field'          => 'street_name',
			'condition'      => 'LIKE',
		];

		$filters['unit_number'] = [
			'#type'          => 'search',
			'#title'         => t('Unit Number'),
			'#placeholder'   => t('Search by unit number'),
			'#default_value' => !empty($values['unit_number']) ? $values['unit_number'] : '',
			'#size'          => 25,
			'field'          => 'unit_number',
			'condition'      => 'LIKE',
		];

		$filters['postal_code'] = [
			'#type'          => 'search',
			'#title'         => t('Postal code'),
			'#placeholder'   => t('Search by postal code'),
			'#default_value' => !empty($values['postal_code']) ? $values['postal_code'] : '',
			'#size'          => 25,
			'field'          => 'postal_code',
			'condition'      => 'LIKE',
		];

		$filters['from_date'] = [
			'#type'          => 'textfield',
			'#title'         => t('From Date'),
			'#attributes'    => [
				'class'    => ['calendar start-date'],
				'readonly' => TRUE
			],
			'#default_value' => !empty($values['from_date']) ? Drupal::service('date.formatter')
			                                                         ->format($values['from_date'],
				                                                         'custom',
				                                                         'Y-m-d h:m') : '',
			'#size'          => 25,
			'#placeholder'   => t('From date'),
			'field'          => 'order_date',
			'condition'      => '>='
		];

		$filters['to_date'] = [
			'#type'          => 'textfield',
			'#title'         => t('To Date'),
			'#attributes'    => [
				'class'    => ['calendar end-date'],
				'readonly' => TRUE
			],
			'#default_value' => !empty($values['to_date']) ? Drupal::service('date.formatter')
			                                                       ->format($values['to_date'],
				                                                       'custom', 'Y-m-d h:m') : '',
			'#size'          => 25,
			'#placeholder'   => t('To date'),
			'field'          => 'order_date',
			'condition'      => '<='
		];

		return $filters;
	}

	/**
	 * @param array $values
	 *
	 * @return array
	 */
	protected function _processFormValues(array $values){
		$filters = [];
		foreach ($values as $field => $value){
			if ($value !== NULL && $value !== ''){
				if ($field == 'from_date' || $field == 'to_date'){
					$value = strtotime(trim($value));
				}

				$filters[$field] = $value;
			}
		}

		return $filters;
	}
}