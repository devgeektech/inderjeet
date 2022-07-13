<?php

namespace Drupal\singpost_toolbox_calculate_postage\Form\Dimension;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Url;
use Drupal\singpost_base\ModelInterface;
use Drupal\singpost_toolbox_calculate_postage\Repositories\DimensionRepository;

/**
 * Class DimensionForm
 *
 * @package Drupal\singpost_toolbox_calculate_postage\Form\Dimension
 */
class DimensionForm extends FormBase{

	use MessengerTrait;

	/**
	 * @var DimensionRepository
	 */
	protected $_service;

	/**
	 * @var ModelInterface
	 */
	protected $_dimension;

	/**
	 * DimensionForm constructor.
	 *
	 * @param DimensionRepository $service
	 * @param \Drupal\singpost_base\ModelInterface $dimension
	 */
	public function __construct(
		DimensionRepository $service,
		ModelInterface $dimension){
		$this->_service   = $service;
		$this->_dimension = $dimension;
	}

	/**
	 * @inheritDoc
	 */
	public function getFormId(){
		return 'dimension_form';
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 *
	 * @return array
	 */
	public function buildForm(array $form, FormStateInterface $form_state){
		$form['size_code'] = [
			'#type'          => 'textfield',
			'#title'         => t('Size Code'),
			'#required'      => TRUE,
			'#maxlength'     => 255,
			'#default_value' => $this->_dimension->size_code ?? '',
		];

		$form['text'] = [
			'#type'          => 'textfield',
			'#title'         => t('Text'),
			'#required'      => TRUE,
			'#maxlength'     => 255,
			'#default_value' => $this->_dimension->text ?? '',
		];

		$form['value'] = [
			'#type'          => 'textfield',
			'#title'         => t('Value'),
			'#required'      => TRUE,
			'#maxlength'     => 255,
			'#default_value' => $this->_dimension->value ?? '',
		];

		$form['length'] = [
			'#type'          => 'textfield',
			'#title'         => t('Length'),
			'#required'      => TRUE,
			'#maxlength'     => 255,
			'#default_value' => $this->_dimension->length ?? '',
		];

		$form['width'] = [
			'#type'          => 'textfield',
			'#title'         => t('Width'),
			'#required'      => TRUE,
			'#maxlength'     => 255,
			'#default_value' => $this->_dimension->width ?? '',
		];

		$form['height'] = [
			'#type'          => 'textfield',
			'#title'         => t('Height'),
			'#required'      => TRUE,
			'#maxlength'     => 255,
			'#default_value' => $this->_dimension->height ?? '',
		];

		$form['published'] = [
			'#type'          => 'checkbox',
			'#title'         => t('Published?'),
			'#default_value' => $this->_dimension->published ?? TRUE,
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
			'#url'        => Url::fromRoute('singpost.toolbox.calculate.dimension.manage'),
		];

		if (!$this->_dimension->is_new){
			$form['actions']['delete'] = [
				'#type'       => 'link',
				'#title'      => $this->t('Delete'),
				'#attributes' => ['class' => ['button button--danger']],
				'#url'        => Url::fromRoute('singpost.toolbox.calculate.admin.dimension.delete',
					['id' => $this->_dimension->id]),
			];
		}

		return $form;
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 */
	public function submitForm(array &$form, FormStateInterface $form_state){
		$form_state->cleanValues();

		$this->_dimension->load([
			'size_code' => $form_state->getValue('size_code'),
			'text'      => $form_state->getValue('text'),
			'value'     => $form_state->getValue('value'),
			'length'    => $form_state->getValue('length'),
			'width'     => $form_state->getValue('width'),
			'height'    => $form_state->getValue('height'),
			'published' => $form_state->getValue('published'),
		]);

		if ($this->_dimension->is_new){
			$message  = t('Successfully created new Dimension.');
		}else{
			$message  = t('Successfully updated Dimension.');
		}

		$redirect = Url::fromRoute('singpost.toolbox.calculate.dimension.manage');

		if ($this->_dimension->save()){
			$this->messenger()->addMessage($message);
			$form_state->setRedirectUrl($redirect);
		}else{
			$this->messenger()
			     ->addError(t('Something went wrong. Cannot save Dimension.'));
			$form_state->setRebuild();
		}
	}


	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 */
	public function validateForm(array &$form, FormStateInterface $form_state){
		$size_code = $form_state->getValue('size_code');
		$text      = $form_state->getValue('text');
		$value     = $form_state->getValue('value');
		$length    = $form_state->getValue('length');
		$width     = $form_state->getValue('width');
		$height    = $form_state->getValue('height');

		if (!$size_code){
			$form_state->setErrorByName('size_code',
				t('Please enter your size code.'));
		}

		if (!$text){
			$form_state->setErrorByName('text',
				t('Please enter your text.'));
		}

		if (!$value){
			$form_state->setErrorByName('value',
				t('Please enter your value.'));
		}

		if (!$length){
			$form_state->setErrorByName('length',
				t('Please enter your length.'));
		}

		if (!is_numeric($length)){
			$form_state->setErrorByName('length',
				t('Lenght must be a number.'));
		}

		if (!$width){
			$form_state->setErrorByName('width',
				t('Please enter your width.'));
		}

		if (!is_numeric($width)){
			$form_state->setErrorByName('width',
				t('Width must be a number.'));
		}

		if (!$height){
			$form_state->setErrorByName('height',
				t('Please enter your height.'));
		}

		if (!is_numeric($height)){
			$form_state->setErrorByName('height',
				t('Height must be a number.'));
		}
	}

}