<?php

namespace Drupal\singpost_toolbox_calculate_postage\Form\Dimension;

use Drupal\Core\Form\FormStateInterface;
use Drupal\singpost_base\Form\BaseSearchForm;
use Drupal\singpost_toolbox_calculate_postage\Repositories\DimensionRepository;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class DimensionSearchForm
 *
 * @package Drupal\singpost_toolbox_calculate_postage\Form\Dimension
 */
class DimensionSearchForm extends BaseSearchForm{

	/**
	 * @var DimensionRepository
	 */
	protected $_dimension;

	/**
	 * DimensionSearchForm constructor.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @param \Drupal\singpost_toolbox_calculate_postage\Repositories\DimensionRepository $dimension
	 */
	public function __construct(
		Request $request,
		DimensionRepository $dimension){
		parent::__construct($request);

		$this->_dimension = $dimension;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFormId(){
		return 'dimension_search_form';
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildForm(array $form, FormStateInterface $form_state){
		$form['#attached']['library'][] = 'singpost_base/datetimepicker';

		$form['filters'] = [
			'#type'       => 'container',
			'#title'      => t('Search Dimension'),
			'#attributes' => ['class' => ['form--inline clearfix']],
		];

		return parent::buildForm($form, $form_state);
	}

	/**
	 * @inheritDoc
	 */
	public function searchFilters($default_values = []){
		$filters['size_code'] = [
			'#type'          => 'search',
			'#title'         => t('Size code'),
			'#placeholder'   => t('Search by size code'),
			'#default_value' => !empty($default_values['size_code']) ? $default_values['size_code'] : '',
			'#size'          => 30,
			'field'          => 'size_code',
			'condition'      => 'LIKE',
		];

		$filters['published'] = [
			'#type'          => 'select',
			'#title'         => t('Status'),
			'#attributes'    => [
				'class' => ['select2'],
			],
			'#default_value' => $default_values['published'] ?? '',
			'#options'       => [
				'' => t('All'),
				1  => t('Published'),
				0  => t('Unpublished'),
			],
			'field'          => 'published',
			'condition'      => '=',
		];

		return $filters;
	}

}