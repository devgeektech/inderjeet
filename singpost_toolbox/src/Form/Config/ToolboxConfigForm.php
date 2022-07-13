<?php


namespace Drupal\singpost_toolbox\Form\Config;


use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ToolboxConfigForm
 *
 * @package Drupal\singpost_toolbox\Form\Config
 */
class ToolboxConfigForm extends ConfigFormBase{

	public static $config_name = 'singpost.toolbox.config';

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
		return 'toolbox_config_form';
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 *
	 * @return array
	 */
	public function buildForm(array $form, FormStateInterface $form_state){
		$setting = $this->config(self::$config_name);

		$form['toolbox_auth_token'] = [
			'#type'          => 'textfield',
			'#title'         => $this->t('Authentication Token'),
			'#default_value' => $setting->get('toolbox_auth_token') ?? '',
		];

		$form['toolbox_auth_token_locate_us'] = [
			'#type'          => 'textfield',
			'#title'         => $this->t('Authentication Token Locate Us'),
			'#default_value' => $setting->get('toolbox_auth_token_locate_us') ?? '',
		];

		return parent::buildForm($form, $form_state);
	}

	/**
	 * @inheritDoc
	 */
	public function submitForm(array &$form, FormStateInterface $form_state){
		$this->config(self::$config_name)
		     ->set('toolbox_auth_token', $form_state->getValue('toolbox_auth_token'))
		     ->set('toolbox_auth_token_locate_us',
			     $form_state->getValue('toolbox_auth_token_locate_us'))
		     ->save();

		parent::submitForm($form, $form_state);
	}
}