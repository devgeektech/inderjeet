<?php


namespace Drupal\singpost_toolbox_locate_us\Form\Config;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ToolboxConfigForm
 *
 * @package Drupal\singpost_toolbox\Form\Config
 */
class LocateUsConfigForm extends ConfigFormBase{

	public static $config_name = 'locate_us.config';

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
		return 'locate_us_config_form';
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 *
	 * @return array
	 */
	public function buildForm(array $form, FormStateInterface $form_state){
		$setting = $this->config(self::$config_name);

		$form['locate_us_url'] = [
			'#type'          => 'url',
			'#title'         => $this->t('Locate Us URL'),
			'#default_value' => $setting->get('locate_us_url') ?? '',
			'#required'      => TRUE,
		];

		$form['locate_us_google_map_key'] = [
			'#type'          => 'textfield',
			'#title'         => $this->t('Google Maps API Key'),
			'#default_value' => $setting->get('locate_us_google_map_key') ?? '',
			'#required'      => TRUE,
		];

		$form['locate_us_limt_item'] = [
			'#type'          => 'textfield',
			'#title'         => $this->t('Maximum Location To Display'),
			'#default_value' => $setting->get('locate_us_limt_item') ?? '',
			'#required'      => TRUE,
		];

		$form['locate_us_api_log'] = [
			'#type'          => 'checkbox',
			'#title'         => $this->t('Log Locate Us ?'),
			'#default_value' => $setting->get('locate_us_api_log') ?? '',
		];

		$form['locate_us_error_message'] = [
			'#type'          => 'textarea',
			'#cols'          => 5,
			'#title'         => $this->t('Error Message'),
			'#default_value' => $setting->get('locate_us_error_message') ?? '',
		];
		
		$form['locate_us_support_card_popstation'] = [
			'#type'          => 'text_format',
			'#title'         => $this->t('Supported Credit/Debit Cards at POPStation'),
			'#format'        => $setting->get('locate_us_support_card_popstation')['format'] ?? 'full_html',
			'#default_value' => $setting->get('locate_us_support_card_popstation')['value'] ?? ''
		];

		return parent::buildForm($form, $form_state);
	}

	/**
	 * @inheritDoc
	 */
	public function submitForm(array &$form, FormStateInterface $form_state){
		$this->config(self::$config_name)
		     ->set('locate_us_url', $form_state->getValue('locate_us_url'))
		     ->set('locate_us_google_map_key',
			     $form_state->getValue('locate_us_google_map_key'))
		     ->set('locate_us_error_message',
			     $form_state->getValue('locate_us_error_message'))
			 ->set('locate_us_limt_item',
			     $form_state->getValue('locate_us_limt_item'))
			->set('locate_us_api_log',
			     $form_state->getValue('locate_us_api_log'))
			->set('locate_us_support_card_popstation',
			     $form_state->getValue('locate_us_support_card_popstation'))
		     ->save();

		parent::submitForm($form, $form_state);
	}

}