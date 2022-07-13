<?php

namespace Drupal\singpost_toolbox_locate_us\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\singpost_base\Form\BaseSearchForm;
use Drupal\singpost_toolbox_locate_us\Repositories\LocateUsRepository;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class LocateUsTypeSearchForm
 *
 * @package Drupal\singpost_toolbox_locate_us\Form
 */
class LocateUsTypeSearchForm extends BaseSearchForm{

	/**
	 * @var LocateUsRepository
	 */
	protected $_locate_us_type;

	/**
	 * LocateUsTypeSearchForm constructor.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @param \Drupal\singpost_toolbox_locate_us\Repositories\LocateUsRepository $locate_us_type
	 */
	public function __construct(
		Request $request,
		LocateUsRepository $locate_us_type){
		parent::__construct($request);

		$this->_locate_us_type = $locate_us_type;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFormId(){
		return 'locate_us_type_search_form';
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildForm(array $form, FormStateInterface $form_state){
		$form['#attached']['library'][] = 'singpost_base/datetimepicker';

		$form['filters'] = [
			'#type'       => 'container',
			'#title'      => t('Search Locate Us Type'),
			'#attributes' => ['class' => ['form--inline clearfix']],
		];

		return parent::buildForm($form, $form_state);
	}

	/**
	 * @inheritDoc
	 */
	public function searchFilters($default_values = []){
		$filters['title'] = [
			'#type'          => 'search',
			'#title'         => t('Title'),
			'#placeholder'   => t('Search by title'),
			'#default_value' => !empty($default_values['title']) ? $default_values['title'] : '',
			'#size'          => 30,
			'field'          => 'title',
			'condition'      => 'LIKE',
		];

		$filters['status'] = [
			'#type'          => 'select',
			'#title'         => t('Status'),
			'#attributes'    => [
				'class' => ['select2'],
			],
			'#default_value' => $default_values['status'] ?? '',
			'#options'       => [
				'' => t('All'),
				1  => t('Published'),
				0  => t('Unpublished'),
			],
			'field'          => 'status',
			'condition'      => '=',
		];

		return $filters;
	}

}