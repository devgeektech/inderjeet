<?php


namespace Drupal\singpost_packing_material\Form\Order;


use Drupal;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\singpost_base\Support\FormHelper;
use Drupal\singpost_packing_material\Repositories\OrderRepository;

/**
 * Class OrderTableForm
 *
 * @package Drupal\singpost_packing_material\Form\Order
 */
class OrderTableForm implements FormInterface{

	/**
	 * @var \Drupal\singpost_packing_material\Repositories\OrderRepository
	 */
	protected $_service;

	/**
	 * @var array
	 */
	protected $_filters;

	/**
	 * OrderTableForm constructor.
	 *
	 * @param \Drupal\singpost_packing_material\Repositories\OrderRepository $repository
	 * @param array $filters
	 */
	public function __construct(OrderRepository $repository, array $filters){
		$this->_service = $repository;
		$this->_filters = $filters;
	}

	/**
	 * @return string
	 */
	public function getFormId(){
		return 'pm_order_table_form';
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 *
	 * @return array
	 */
	public function buildForm(array $form, FormStateInterface $form_state){
		$limit = FormHelper::getPaginationLimitFromRequest(30);

		$header = [
			['data' => t('ID'), 'field' => 'id'],
			['data' => t('Name'), 'field' => 'name'],
			['data' => t('Block Num.'), 'field' => 'block_number'],
			['data' => t('Str. Address'), 'field' => 'street_name'],
			['data' => t('Unit Num.'), 'field' => 'unit_number'],
			['data' => t('Postal Code'), 'field' => 'postal_code'],
			['data' => t('Company Name'), 'field' => 'company_name'],
			['data' => t('Contact Num.'), 'field' => 'contact_number'],
			['data' => t('Total'), 'field' => 'total'],
			['data' => t('Order Date'), 'field' => 'order_date'],
			'actions' => t('Operations'),
		];

		$pager = $this->_service->applyFilters($this->_filters)
		                        ->getTablePaginatedData($header, $limit);

		$orders = $this->_service->getTableData($pager);

		$form['form'] = [
			'#type'       => 'container',
			'#attributes' => ['class' => ['form--inline clearfix']]
		];

		$form['form']['pagination'] = FormHelper::createPaginationLimit('singpost.pm.order.manage',
			$limit);

		$form['table'] = [
			'#type'       => 'table',
			'#header'     => $header,
			'#attributes' => [
				'id' => $this->getFormId()
			]
		];

		if (!empty($orders)){
			foreach ($orders as $item){
				$form['table'][$item->id] = [
					'id'             => [
						'#plain_text' => t('#') . sprintf("%06d", $item->id)
					],
					'name'           => [
						'#plain_text' => $item->name
					],
					'block_number'   => [
						'#plain_text' => $item->block_number
					],
					'street_name'    => [
						'#plain_text' => $item->street_name
					],
					'unit_number'    => [
						'#plain_text' => $item->unit_number
					],
					'postal_code'    => [
						'#plain_text' => $item->postal_code
					],
					'company_name'   => [
						'#plain_text' => $item->company_name
					],
					'contact_number' => [
						'#plain_text' => $item->contact_number
					],
					'total'          => [
						'#plain_text' => t('S$') . $item->total
					],
					'order_date'     => [
						'#plain_text' => Drupal::service('date.formatter')
						                       ->format($item->order_date, 'custom', 'Y-m-d h:m')
					],
					'actions'        => [
						'#type'  => 'dropbutton',
						'#links' => [
							'edit' => [
								'title' => t('View'),
								'url'   => Url::fromRoute('singpost.pm.order.view',
									['id' => $item->id])
							]
						],
					]
				];
			}

			$form['count'] = $pager->renderSummary();
		}else{
			$form['table']['#empty'] = t('No order found.');
		}

		return $form;
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 */
	public function validateForm(array &$form, FormStateInterface $form_state){ }

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 */
	public function submitForm(array &$form, FormStateInterface $form_state){ }
}