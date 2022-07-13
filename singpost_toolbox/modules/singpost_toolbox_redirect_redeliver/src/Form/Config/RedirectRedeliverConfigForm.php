<?php


namespace Drupal\singpost_toolbox_redirect_redeliver\Form\Config;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ToolboxConfigForm
 *
 * @package Drupal\singpost_toolbox\Form\Config
 */
class RedirectRedeliverConfigForm extends ConfigFormBase{

	public static $config_name = 'redirect_redeliver.config';

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
		return 'redirect_redeliver_config_form';
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 *
	 * @return array
	 */
	public function buildForm(array $form, FormStateInterface $form_state){
		$setting = $this->config(self::$config_name);

		$form['redirect_url'] = [
			'#type'          => 'url',
			'#title'         => $this->t('Redirect URL'),
			'#default_value' => $setting->get('redirect_url') ?? '',
			'#required'      => TRUE,
		];

		$form['redeliver_url'] = [
			'#type'          => 'url',
			'#title'         => $this->t('Redeliver URL'),
			'#default_value' => $setting->get('redeliver_url') ?? '',
			'#required'      => TRUE,
		];

		$form['rr_error_message'] = [
			'#type'          => 'textarea',
			'#cols'          => 5,
			'#title'         => $this->t('Error Message'),
			'#default_value' => $setting->get('rr_error_message') ?? '',
		];

		return parent::buildForm($form, $form_state);
	}

	/**
	 * @inheritDoc
	 */
	public function submitForm(array &$form, FormStateInterface $form_state){
		$this->config(self::$config_name)
		     ->set('redeliver_url', $form_state->getValue('redeliver_url'))
		     ->set('redirect_url', $form_state->getValue('redirect_url'))
		     ->set('rr_error_message', $form_state->getValue('rr_error_message'))
		     ->save();

		parent::submitForm($form, $form_state);
	}
}