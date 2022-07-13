<?php


namespace Drupal\singpost_packing_material\Form\Config;


use Drupal;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class PackingMaterialConfigForm
 *
 * @package Drupal\singpost_packing_material\Form\Config
 */
class PackingMaterialConfigForm extends ConfigFormBase{

	/**
	 * @var string
	 */
	public static $config_name = 'singpost.pm.settings';

	/**
	 * @return string
	 */
	public function getFormId(){
		return 'pm_setting_form';
	}

	/**
	 * @return array
	 */
	protected function getEditableConfigNames(){
		return [self::$config_name];
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 *
	 * @return array
	 */
	public function buildForm(array $form, FormStateInterface $form_state){
		$setting = $this->config(self::$config_name);

		$encryption_options = Drupal::service('encrypt.encryption_profile.manager')
		                            ->getEncryptionProfileNamesAsOptions();

		$form['pm_encryption'] = [
			'#type'          => 'select',
			'#title'         => $this->t('Select Encryption Profile'),
			'#options'       => $encryption_options,
			'#default_value' => $setting->get('pm_encryption') ? $setting->get('pm_encryption') : ''
		];

		$form['pm_notice'] = [
			'#type'          => 'text_format',
			'#title'         => $this->t('Notice page'),
			'#format'        => $setting->get('pm_notice')['format'] ?? 'basic_html',
			'#default_value' => $setting->get('pm_notice')['value'] ?? ''
		];

		$form['pm_confirmation_page'] = [
			'#type'          => 'text_format',
			'#title'         => $this->t('Content Confirmation Page'),
			'#format'        => $setting->get('pm_confirmation_page')['format'] ?? 'basic_html',
			'#default_value' => $setting->get('pm_confirmation_page')['value'] ?? ''
		];

		$form['notify_staff'] = [
			'#type'  => 'details',
			'#title' => t('Message Staff'),
			'#open'  => FALSE,
			'#tree'  => TRUE
		];

		$form['notify_staff']['staff_to_email'] = [
			'#type'          => 'textfield',
			'#title'         => t('To email'),
			'#default_value' => $setting->get('staff_to_email') ?? '',
			'#description'   => t('Multiple email addresses may be separated by commas.')
		];

		$form['notify_staff']['staff_from_email'] = [
			'#type'          => 'textfield',
			'#title'         => t('From email'),
			'#default_value' => $setting->get('staff_from_email') ?? '',
			'#description'   => t('Multiple email addresses may be separated by commas.')
		];

		$form['notify_staff']['staff_subject'] = [
			'#type'          => 'textfield',
			'#title'         => t('Subject'),
			'#default_value' => $setting->get('staff_subject') ?? '',
		];

		$form['notify_staff']['staff_message'] = [
			'#type'          => 'text_format',
			'#title'         => $this->t('Message'),
			'#format'        => $setting->get('staff_message')['format'] ?? 'basic_html',
			'#default_value' => $setting->get('staff_message')['value'] ?? '',
			'#description'   => t('Available variables are: [order:id], [order:detail], [order:date], [customer:name], [customer:company], [customer:email], [customer:contact_number], [customer:account_number], [customer:delivery_address]')
		];

		$form['notify_customer'] = [
			'#type'  => 'details',
			'#title' => t('Message Customer'),
			'#open'  => FALSE,
			'#tree'  => TRUE
		];

		$form['notify_customer']['customer_from_email'] = [
			'#type'          => 'textfield',
			'#title'         => t('From email'),
			'#default_value' => $setting->get('customer_from_email') ?? '',
			'#description'   => t('Multiple email addresses may be separated by commas.'),
		];

		$form['notify_customer']['customer_subject'] = [
			'#type'          => 'textfield',
			'#title'         => t('Subject'),
			'#default_value' => $setting->get('customer_subject') ?? '',
		];

		$form['notify_customer']['customer_message'] = [
			'#type'          => 'text_format',
			'#title'         => $this->t('Message'),
			'#format'        => $setting->get('customer_message')['format'] ?? 'basic_html',
			'#default_value' => $setting->get('customer_message')['value'] ?? '',
			'#description'   => t('Available variables are: [order:id], [order:detail], [order:date], [customer:name], [customer:company], [customer:email], [customer:contact_number], [customer:account_number], [customer:delivery_address]')
		];

		return parent::buildForm($form, $form_state);
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 */
	public function submitForm(array &$form, FormStateInterface $form_state){
		$staff    = $form_state->getValue('notify_staff');
		$customer = $form_state->getValue('notify_customer');

		$this->config(self::$config_name)
		     ->set('pm_encryption', $form_state->getValue('pm_encryption'))
		     ->set('pm_notice', $form_state->getValue('pm_notice'))
		     ->set('pm_confirmation_page', $form_state->getValue('pm_confirmation_page'))
		     ->set('staff_to_email', $staff['staff_to_email'])
		     ->set('staff_from_email', $staff['staff_from_email'])
		     ->set('staff_subject', $staff['staff_subject'])
		     ->set('staff_message', $staff['staff_message'])
		     ->set('customer_from_email', $customer['customer_from_email'])
		     ->set('customer_subject', $customer['customer_subject'])
		     ->set('customer_message', $customer['customer_message'])
		     ->save();

		parent::submitForm($form, $form_state);
	}
}