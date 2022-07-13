<?php

namespace Drupal\singpost_toolbox_calculate_postage\Form\Dimension;

use Drupal;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Url;
use Drupal\singpost_base\Support\FormHelper;
use Drupal\singpost_toolbox_calculate_postage\Repositories\DimensionRepository;

/**
 * Class DimensionTableForm
 *
 * @package Drupal\singpost_toolbox_calculate_postage\Form\Dimension
 */
class DimensionTableForm implements FormInterface{

	use MessengerTrait;

	/**
	 * @var \Drupal\singpost_toolbox_calculate_postage\Repositories\DimensionRepository
	 */
	protected $_dimension;

	/**
	 * @var array
	 */
	protected $_filters;

	/**
	 * DimensionTableForm constructor.
	 *
	 * @param DimensionRepository $dimension
	 * @param array $filters
	 */
	public function __construct(
		DimensionRepository $dimension,
		array $filters){
		$this->_dimension = $dimension;
		$this->_filters   = $filters;
	}

	/**
	 * @inheritDoc
	 */
	public function getFormId(){
		return 'dimension_table_form';
	}

	/**
	 * @inheritDoc
	 */
	public function buildForm(array $form, FormStateInterface $form_state){
		$limit = FormHelper::getPaginationLimitFromRequest(30);

		$header = [
			['data' => t('ID'), 'field' => 'id'],
			['data' => t('Size Code'), 'field' => 'size_code'],
			['data' => t('Weight'), 'field' => 'weight', 'sort' => 'asc'],
			['data' => t('Text'), 'field' => 'text'],
			['data' => t('Value'), 'field' => 'value'],
			['data' => t('Length'), 'field' => 'length'],
			['data' => t('Width'), 'field' => 'width'],
			['data' => t('Height'), 'field' => 'height'],
			['data' => t('Published'), 'field' => 'published'],
			'actions' => t('Operations'),
		];

		$pager = $this->_dimension->applyFilters($this->_filters)
		                          ->getTablePaginatedData($header, $limit);

		$dimensions = $this->_dimension->getTableData($pager);

		$form['form'] = [
			'#type'       => 'container',
			'#attributes' => ['class' => ['form--inline clearfix']],
		];

		$form['form']['pagination'] = FormHelper::createPaginationLimit('singpost.toolbox.calculate.dimension.manage',
			$limit);

		$form['table'] = [
			'#type'       => 'table',
			'#header'     => $header,
			'#tabledrag'  => [
				[
					'action'       => 'order',
					'relationship' => 'sibling',
					'group'        => 'order-weight',
				]
			],
			'#attributes' => [
				'id' => $this->getFormId(),
			],
		];

		$form['actions'] = [
			'#type'       => 'container',
			'#attributes' => ['class' => ['form-actions js-form-wrapper form-wrapper']]
		];

		$form['actions']['save'] = [
			'#type'       => 'submit',
			'#value'      => 'Save',
			'#attributes' => ['class' => ['button']]
		];

		if (!empty($dimensions)){
			foreach ($dimensions as $dimension){
				$form['table'][$dimension->id] = [
					'#attributes' => ['class' => ['draggable']],
					'id'          => [
						'#plain_text' => $dimension->id
					],
					'size_code'   => [
						'#plain_text' => $dimension->size_code,
					],
					'#weight'     => $dimension->weight,
					'weight'      => [
						'#type'          => 'weight',
						'#title'         => t('Weight for @size_code',
							['@size_code' => $dimension->size_code]),
						'#title_display' => 'invisible',
						'#default_value' => $dimension->weight,
						'#attributes'    => ['class' => ['order-weight']]
					],
					'text'        => [
						'#plain_text' => $dimension->text,
					],
					'value'       => [
						'#plain_text' => $dimension->value,
					],
					'length'      => [
						'#plain_text' => $dimension->length,
					],
					'width'       => [
						'#plain_text' => $dimension->width,
					],
					'height'      => [
						'#plain_text' => $dimension->height,
					],
					'published'   => [
						'#theme'   => 'toggle_button',
						'#nid'     => $dimension->id,
						'#checked' => $dimension->published,
						'#action'  => Url::fromRoute('singpost.toolbox.calculate.admin.dimension.status'),
					],
					'actions'     => [
						'#type'  => 'dropbutton',
						'#links' => [
							'edit'   => [
								'title' => t('Edit'),
								'url'   => Url::fromRoute('singpost.toolbox.calculate.admin.dimension.edit',
									[
										'id' => $dimension->id,
									]),
							],
							'delete' => [
								'title' => t('Delete'),
								'url'   => Url::fromRoute('singpost.toolbox.calculate.admin.dimension.delete',
									[
										'id' => $dimension->id,
									]),
							],
						],
					],
				];
			}

			$form['count'] = $pager->renderSummary();
		}else{
			$form['table']['#empty'] = t('No dimension found.');
		}

		return $form;
	}

	/**
	 * @inheritDoc
	 */
	public function validateForm(array &$form, FormStateInterface $form_state){
		if (!array_filter($form_state->getValue('table'))){
			$form_state->setErrorByName('table',
				t('Please select at least one item'));
		}
	}

	/**
	 * @inheritDoc
	 */
	public function submitForm(array &$form, FormStateInterface $form_state){
		$selected_ids = $form_state->getValue('table');
		$new_arr      = [];
		foreach ($selected_ids as $id => $weight){
			if (is_numeric($id)){
				$new_arr += [$id => $weight['weight']];
			}
		}

		$selected_ids = array_filter($new_arr);

		foreach ($selected_ids as $id => $weight){
			$this->_dimension->getModel()::findOrFail($id)->updateAttributes(['weight' => $weight]);
		}

		Drupal::messenger()->addStatus(t('Dimension have been updated.'));
		$form_state->setRedirect('singpost.toolbox.calculate.dimension.manage');
	}

}