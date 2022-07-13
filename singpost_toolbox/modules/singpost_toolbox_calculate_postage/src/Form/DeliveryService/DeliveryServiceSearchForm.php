<?php


namespace Drupal\singpost_toolbox_calculate_postage\Form\DeliveryService;


use Drupal\Core\Form\FormStateInterface;
use Drupal\singpost_base\Form\BaseSearchForm;
use Drupal\singpost_toolbox_calculate_postage\Repositories\DeliveryServiceRepository;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class DeliveryServiceSearchForm
 *
 * @package Drupal\singpost_toolbox_calculate_postage\Form\DeliveryService
 */
class DeliveryServiceSearchForm extends BaseSearchForm{

	/**
	 * @var \Drupal\singpost_toolbox_calculate_postage\Repositories\DeliveryServiceRepository
	 */
	protected $_repository;

	/**
	 * DeliveryServiceSearchForm constructor.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @param \Drupal\singpost_toolbox_calculate_postage\Repositories\DeliveryServiceRepository $repository
	 */
	public function __construct(Request $request, DeliveryServiceRepository $repository){
		parent::__construct($request);
		$this->_repository = $repository;
	}

	/**
	 * @return string
	 */
	public function getFormId(){
		return 'delivery_service_search_form';
	}

	public function buildForm(array $form, FormStateInterface $form_state){
		$form['filters'] = [
			'#type'       => 'container',
			'#title'      => t('Delivery Service Name'),
			'#attributes' => ['class' => ['form--inline clearfix']],
		];

		return parent::buildForm($form, $form_state);
	}

	/**
	 * @param array $values
	 *
	 * @return array|void
	 */
	public function searchFilters($values = []){
		$filters['delivery_service_name'] = [
			'#type'          => 'search',
			'#title'         => t('Delivery Service Name'),
			'#placeholder'   => t('Search by Service Name'),
			'#default_value' => !empty($values['delivery_service_name']) ? $values['delivery_service_name'] : '',
			'#size'          => 30,
			'field'          => 'delivery_service_name',
			'condition'      => 'LIKE',
		];

		$filters['display_name'] = [
			'#type'          => 'search',
			'#title'         => t('Display Name'),
			'#placeholder'   => t('Search by Display Name'),
			'#default_value' => !empty($values['display_name']) ? $values['display_name'] : '',
			'#size'          => 30,
			'field'          => 'display_name',
			'condition'      => 'LIKE',
		];

		$filters['published'] = [
			'#type'          => 'select',
			'#title'         => t('Published'),
			'#default_value' => $values['published'] ?? '',
			'#options'       => [
				'' => t('All'),
				1  => t('Published'),
				0  => t('Unpublished')
			],
			'field'          => 'published',
			'condition'      => '='
		];

		return $filters;
	}
}