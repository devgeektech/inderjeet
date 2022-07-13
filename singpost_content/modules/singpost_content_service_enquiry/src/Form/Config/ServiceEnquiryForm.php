<?php

namespace Drupal\singpost_content_service_enquiry\Form\Config;

use Drupal;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ServiceEnquiryForm
 *
 * @package Drupal\singpost_content_service_enquiry\Form\Config
 */
class ServiceEnquiryForm extends ConfigFormBase{

	public static $config_name = 'singpost.webform.service_enquiry';

	/**
	 * @inheritDoc
	 */
	protected function getEditableConfigNames(){
		return [self::$config_name];
	}

	/**
	 * @inheritDoc
	 */
	public function getFormId(){
		return 'service_enquiry_settings';
	}

	/**
	 * @inheritDoc
	 */
	public function buildForm(array $form, FormStateInterface $form_state){
		$setting = $this->config(self::$config_name);

		$form['webform_id'] = [
			'#type'          => 'select',
			'#title'         => $this->t('Webform'),
			'#default_value' => $setting->get('webform_id'),
			'#options'       => Drupal::entityQuery('webform')->execute(),
			"#empty_option"  => t('- Select -'),
			'#required'      => TRUE
		];

		$form['map_data'] = [
			'#type'          => 'webform_codemirror',
			'#mode'          => 'yaml',
			'#title'         => $this->t('Service Type data mapping'),
			'#default_value' => $setting->get('map_data'),
		];

		return parent::buildForm($form, $form_state);
	}

	/**
	 * @inheritDoc
	 */
	public function submitForm(array &$form, FormStateInterface $form_state){
		$this->config(self::$config_name)
		     ->set('webform_id', $form_state->getValue('webform_id'))
		     ->set('map_data', $form_state->getValue('map_data'))
		     ->save();

		parent::submitForm($form, $form_state);
	}
}