<?php

namespace Drupal\singpost_toolbox_locate_us\Form;

use Drupal;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Url;
use Drupal\singpost_base\Support\FormHelper;
use Drupal\singpost_toolbox_locate_us\Repositories\LocateUsRepository;

/**
 * Class LocateUsTypeTableForm
 *
 * @package Drupal\singpost_announcements\Form
 */
class LocateUsTypeTableForm implements FormInterface{

	use MessengerTrait;

	/**
	 * @var \Drupal\singpost_toolbox_locate_us\Repositories\LocateUsRepository
	 */
	protected $_locate_us_type;

	/**
	 * @var array
	 */
	protected $_filters;

	/**
	 * LocateUsTypeTableForm constructor.
	 *
	 * @param LocateUsRepository $locate_us_type
	 * @param array $filters
	 */
	public function __construct(
		LocateUsRepository $locate_us_type,
		array $filters){
		$this->_locate_us_type = $locate_us_type;
		$this->_filters        = $filters;
	}

	/**
	 * @inheritDoc
	 */
	public function getFormId(){
		return 'locate_us_type_table_form';
	}

	/**
	 * @inheritDoc
	 */
	public function buildForm(array $form, FormStateInterface $form_state){
		$limit = FormHelper::getPaginationLimitFromRequest(30);

		$header = [
			['data' => t('Title'), 'field' => 'title'],
			['data' => t('Value'), 'field' => 'value'],
			['data' => t('Icon'), 'field' => 'icon'],
			['data' => t('Icon Text'), 'field' => 'icon_text'],
			['data' => t('Marker'), 'field' => 'marker'],
			['data' => t('Published'), 'field' => 'status'],
			'actions' => t('Operations'),
		];

		$pager = $this->_locate_us_type->applyFilters($this->_filters)
		                               ->getTablePaginatedData($header, $limit);

		$locate_us_types = $this->_locate_us_type->getTableData($pager);

		$form['form'] = [
			'#type'       => 'container',
			'#attributes' => ['class' => ['form--inline clearfix']],
		];

		$form['form']['bulk_action'] = [
			'#type'    => 'select',
			'#title'   => t('Action'),
			'#options' => [
				'delete'    => t('Delete'),
				'publish'   => t('Publish'),
				'unpublish' => t('Unpublish'),
			],
		];

		$form['form']['pagination'] = FormHelper::createPaginationLimit('singpost.toolbox.locate_us.admin.type',
			$limit);

		$form['buttons'] = [
			'#type'       => 'container',
			'#attributes' => ['class' => ['form-actions js-form-wrapper form-wrapper']],
		];

		$form['buttons']['apply'] = [
			'#type'       => 'submit',
			'#value'      => 'Apply Action',
			'#attributes' => ['class' => ['button']],
		];

		$form['table'] = [
			'#type'        => 'table',
			'#header'      => $header,
			'#tableselect' => TRUE,
			'#attributes'  => [
				'id' => $this->getFormId(),
			],
		];

		if (!empty($locate_us_types)){
			foreach ($locate_us_types as $locate_us_type){
				if (!empty($locate_us_type->icon)){
					$icon_file = Drupal\file\Entity\File::load($locate_us_type->icon);
					$icon      = [
						'#theme'      => 'image',
						'#uri'        => $icon_file ? $icon_file->getFileUri() : NULL,
						'#attributes' => [
							'style' => 'height: 50px',
						],
					];
				}else{
					$icon = ['#markup' => '<i>No image</i>'];
				}

				if (!empty($locate_us_type->marker)){
					$marker_file = Drupal\file\Entity\File::load($locate_us_type->marker);
					$marker      = [
						'#theme'      => 'image',
						'#uri'        => $marker_file ? $marker_file->getFileUri() : NULL,
						'#attributes' => [
							'style' => 'height: 70px',
						],
					];
				}else{
					$marker = ['#markup' => '<i>No image</i>'];
				}

				$form['table'][$locate_us_type->id] = [
					'title'     => [
						'#plain_text' => $locate_us_type->title,
					],
					'value'     => [
						'#plain_text' => $locate_us_type->value,
					],
					'icon'      => [$icon],
					'icon_text' => [
						'#plain_text' => $locate_us_type->icon_text,
					],
					'marker'    => [$marker],
					'status'    => [
						'#theme'   => 'toggle_button',
						'#nid'     => $locate_us_type->id,
						'#checked' => $locate_us_type->status,
						'#action'  => Url::fromRoute('singpost.toolbox.locate_us.admin.type.status'),
					],
					'actions'   => [
						'#type'  => 'dropbutton',
						'#links' => [
							'edit'   => [
								'title' => t('Edit'),
								'url'   => Url::fromRoute('singpost.toolbox.locate_us.admin.type.edit',
									[
										'id' => $locate_us_type->id,
									]),
							],
							'delete' => [
								'title' => t('Delete'),
								'url'   => Url::fromRoute('singpost.toolbox.locate_us.admin.type.delete',
									[
										'id' => $locate_us_type->id,
									]),
							],
						],
					],
				];
			}

			$form['count'] = $pager->renderSummary();
		}else{
			$form['table']['#empty'] = t('No announcement found.');
		}

		return $form;
	}

	/**
	 * @inheritDoc
	 */
	public function validateForm(array &$form, FormStateInterface $form_state){
		if (!$form_state->getValue('bulk_action')){
			$form_state->setErrorByName('bulk_action', t('Please select an action'));
		}

		if (!array_filter($form_state->getValue('table'))){
			$form_state->setErrorByName('table',
				t('Please select at least one item'));
		}
	}

	/**
	 * @inheritDoc
	 */
	public function submitForm(array &$form, FormStateInterface $form_state){
		$selected = array_filter($form_state->getValue('table'));

		Drupal::request()
		      ->getSession()
		      ->set('bulk_selected_locate_us_types', $selected);
		$form_state->setRedirect('singpost.toolbox.locate_us.admin.type.action',
			['action' => $form_state->getValue('bulk_action')]);
	}

}