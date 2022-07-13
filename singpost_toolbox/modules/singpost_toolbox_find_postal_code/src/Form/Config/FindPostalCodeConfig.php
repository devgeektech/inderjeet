<?php


namespace Drupal\singpost_toolbox_find_postal_code\Form\Config;


use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class FindPostalCodeConfig
 *
 * @package Drupal\singpost_toolbox_find_postal_code\Form\Config
 */
class FindPostalCodeConfig extends ConfigFormBase{

	public static $config_name = 'find_postal_code.config';

	/**
	 * @return array
	 */
	protected function getEditableConfigNames(){
		return [self::$config_name];
	}

	/**
	 * @return string
	 */
	public function getFormId(){
		return 'find_postal_code_config_form';
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 *
	 * @return array
	 */
	public function buildForm(array $form, FormStateInterface $form_state){
		$setting = $this->config(self::$config_name);

		$form['api'] = [
			'#type'  => 'details',
			'#title' => t('API')
		];

		$form['api']['fpc_street_api'] = [
			'#type'          => 'url',
			'#required'      => TRUE,
			'#title'         => $this->t('Street API'),
			'#default_value' => $setting->get('fpc_street_api') ?? '',
		];

		$form['api']['fpc_landmark_api'] = [
			'#type'          => 'url',
			'#required'      => TRUE,
			'#title'         => $this->t('Landmark API'),
			'#default_value' => $setting->get('fpc_landmark_api') ?? '',
		];

		$form['api']['fpc_pobox_api'] = [
			'#type'          => 'url',
			'#required'      => TRUE,
			'#title'         => $this->t('PO Box API'),
			'#default_value' => $setting->get('fpc_pobox_api') ?? '',
		];

		$form['authorize'] = [
			'#type'  => 'details',
			'#title' => t('Authorize')
		];

		$form['authorize']['fpc_authorize_header_name'] = [
			'#type'          => 'textfield',
			'#title'         => $this->t('Authorize Header Name'),
			'#default_value' => $setting->get('fpc_authorize_header_name') ?? 'Authorization',
		];

		$form['authorize']['fpc_authorize_header_key'] = [
			'#type'          => 'textfield',
			'#title'         => $this->t('Authorize Header Key'),
			'#default_value' => $setting->get('fpc_authorize_header_key') ?? '',
		];

		$form['fpc_error_message'] = [
			'#type'          => 'textarea',
			'#cols'          => 5,
			'#title'         => $this->t('Error Message'),
			'#default_value' => $setting->get('fpc_error_message') ?? '',
		];

		return parent::buildForm($form, $form_state);
	}

	/**
	 * @inheritDoc
	 */
	public function submitForm(array &$form, FormStateInterface $form_state){
		$this->config(self::$config_name)
			->set('fpc_street_api', $form_state->getValue('fpc_street_api'))
			->set('fpc_landmark_api', $form_state->getValue('fpc_landmark_api'))
			->set('fpc_pobox_api', $form_state->getValue('fpc_pobox_api'))
			->set('fpc_authorize_header_name', $form_state->getValue('fpc_authorize_header_name'))
			->set('fpc_authorize_header_key', $form_state->getValue('fpc_authorize_header_key'))
			->set('fpc_error_message', $form_state->getValue('fpc_error_message'))
			->save();

		parent::submitForm($form, $form_state);
	}
}