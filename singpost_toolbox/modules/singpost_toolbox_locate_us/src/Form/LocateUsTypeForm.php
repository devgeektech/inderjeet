<?php

namespace Drupal\singpost_toolbox_locate_us\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Url;
use Drupal\singpost_base\ModelInterface;
use Drupal\singpost_toolbox_locate_us\Repositories\LocateUsRepository;

/**
 * Class LocateUsTypeForm
 *
 * @package Drupal\singpost_toolbox_locate_us\Form
 */
class LocateUsTypeForm extends FormBase{

	use MessengerTrait;

	/**
	 * @var LocateUsRepository
	 */
	protected $_service;

	/**
	 * @var ModelInterface
	 */
	protected $_locate_us_type;

	/**
	 * LocateUsTypeForm constructor.
	 *
	 * @param LocateUsRepository $service
	 * @param \Drupal\singpost_base\ModelInterface $locate_us_type
	 */
	public function __construct(
		LocateUsRepository $service,
		ModelInterface $locate_us_type){
		$this->_service        = $service;
		$this->_locate_us_type = $locate_us_type;
	}

	/**
	 * @inheritDoc
	 */
	public function getFormId(){
		return 'locate_us_type_form';
	}

	/**
	 * @inheritDoc
	 */
	public function buildForm(array $form, FormStateInterface $form_state){
		$form['title'] = [
			'#type'          => 'textfield',
			'#title'         => t('Title'),
			'#required'      => TRUE,
			'#maxlength'     => 255,
			'#default_value' => $this->_locate_us_type->title ?? '',
		];

		$form['value'] = [
			'#type'          => 'textfield',
			'#title'         => t('Value'),
			'#required'      => TRUE,
			'#maxlength'     => 255,
			'#default_value' => $this->_locate_us_type->value ?? '',
		];

		$form['status'] = [
			'#type'          => 'checkbox',
			'#title'         => t('Published?'),
			'#default_value' => $this->_locate_us_type->status ?? TRUE,
		];

		$form['icon'] = [
			'#type'              => 'managed_file',
			'#upload_location'   => 'public://upload/locate-us-type/',
			'#title'             => t('Icon'),
			'#required'          => TRUE,
			'#upload_validators' => [
				'file_validate_extensions' => ['gif png jpg jpeg'],
			],
			'#default_value'     => ($this->_locate_us_type->icon) ? [$this->_locate_us_type->icon] : '',
		];

		$form['icon_text'] = [
			'#type'          => 'textfield',
			'#title'         => t('Icon Text'),
			'#required'      => TRUE,
			'#maxlength'     => 255,
			'#default_value' => $this->_locate_us_type->icon_text ?? '',
		];

		$form['marker'] = [
			'#type'              => 'managed_file',
			'#upload_location'   => 'public://upload/locate-us-type/',
			'#title'             => t('Marker'),
			'#required'          => TRUE,
			'#upload_validators' => [
				'file_validate_extensions' => ['gif png jpg jpeg'],
			],
			'#default_value'     => ($this->_locate_us_type->marker) ? [$this->_locate_us_type->marker] : '',
		];

		$form['actions'] = ['#type' => 'actions'];

		$form['actions']['submit'] = [
			'#type'       => 'submit',
			'#value'      => t('Save'),
			'#attributes' => ['class' => ['button button--primary']],
		];

		$form['actions']['cancel'] = [
			'#type'       => 'link',
			'#title'      => t('Cancel'),
			'#attributes' => ['class' => ['button']],
			'#url'        => Url::fromRoute('singpost.toolbox.locate_us.admin.type'),
		];

		if (!$this->_locate_us_type->is_new){
			$form['actions']['delete'] = [
				'#type'       => 'link',
				'#title'      => $this->t('Delete'),
				'#attributes' => ['class' => ['button button--danger']],
				'#url'        => Url::fromRoute('singpost.toolbox.locate_us.admin.type.delete',
					['id' => $this->_locate_us_type->id]),
			];
		}

		return $form;
	}

	/**
	 * @inheritDoc
	 */
	public function submitForm(array &$form, FormStateInterface $form_state){
		$form_state->cleanValues();

		$this->_locate_us_type->load([
			'title'     => $form_state->getValue('title'),
			'value'     => $form_state->getValue('value'),
			'icon'      => $form_state->getValue('icon'),
			'icon_text' => $form_state->getValue('icon_text'),
			'marker'    => $form_state->getValue('marker'),
			'status'    => $form_state->getValue('status'),
		]);

		if ($this->_locate_us_type->is_new){
			$message  = t('Successfully created new Locate Us Type.');
			$redirect = Url::fromRoute('singpost.toolbox.locate_us.admin.type');
		}else{
			$message  = t('Successfully updated Locate Us Type.');
			$redirect = Url::fromRoute('singpost.toolbox.locate_us.admin.type.edit',
				['id' => $this->_locate_us_type->id]);
		}

		if ($this->_locate_us_type->save()){
			$this->messenger()->addMessage($message);
			$form_state->setRedirectUrl($redirect);
		}else{
			$this->messenger()
			     ->addError(t('Something went wrong. Cannot save Locate Us Type.'));
			$form_state->setRebuild();
		}
	}

}