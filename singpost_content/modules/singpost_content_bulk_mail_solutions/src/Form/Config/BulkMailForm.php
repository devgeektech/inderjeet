<?php

namespace Drupal\singpost_content_bulk_mail_solutions\Form\Config;

use Drupal;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class BulkMailForm
 *
 * @package Drupal\singpost_content_bulk_mail_solutions\Form\Config
 */
class BulkMailForm extends ConfigFormBase{

	public static $config_name = 'singpost.webform.bulk_mail_solutions';

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
		return 'bulk_mail_solutions_settings';
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
			'#empty_option'  => t('- Select -'),
			'#required'      => TRUE
		];

		$form['api'] = [
			'#type'  => 'details',
			'#title' => 'API'
		];

		$form['api']['service_url'] = [
			'#type'          => 'url',
			'#title'         => $this->t('Service URL'),
			'#default_value' => $setting->get('service_url'),
			'#required'      => TRUE
		];

		$form['api']['authorize'] = [
			'#type'  => 'details',
			'#title' => t('Authorize')
		];

		$form['api']['authorize']['blk_header_name'] = [
			'#type'          => 'textfield',
			'#title'         => $this->t('Authorize Header Name'),
			'#default_value' => $setting->get('blk_header_name') ?? 'Authorization',
		];

		$form['api']['authorize']['blk_header_key'] = [
			'#type'          => 'textfield',
			'#title'         => $this->t('Authorize Header Key'),
			'#default_value' => $setting->get('blk_header_key') ?? '',
		];

		$form['api']['blk_log_api'] = [
			'#type'          => 'checkbox',
			'#title'         => $this->t('Log Audit Trail?'),
			'#default_value' => $setting->get('blk_log_api') ?? '',
		];

		$form['early_posting_tooltip'] = [
			'#type'          => 'text_format',
			'#title'         => $this->t('Early Posting Incentive tooltip'),
			'#default_value' => $setting->get('early_posting_tooltip') ? $setting->get('early_posting_tooltip')['value'] : NULL,
			'#format'        => $setting->get('early_posting_tooltip') ? $setting->get('early_posting_tooltip')['format'] : 'basic_html',
		];

		$form['non_bulk_mail'] = [
			'#type'          => 'text_format',
			'#title'         => $this->t('Non-Bulk Mail Rates'),
			'#default_value' => $setting->get('non_bulk_mail') ? $setting->get('non_bulk_mail')['value'] : NULL,
			'#format'        => $setting->get('non_bulk_mail') ? $setting->get('non_bulk_mail')['format'] : 'basic_html',
		];

		return parent::buildForm($form, $form_state);
	}

	/**
	 * @inheritDoc
	 */
	public function submitForm(array &$form, FormStateInterface $form_state){
		$this->config(self::$config_name)
		     ->set('webform_id', $form_state->getValue('webform_id'))
		     ->set('service_url', $form_state->getValue('service_url'))
		     ->set('early_posting_tooltip', $form_state->getValue('early_posting_tooltip'))
			 ->set('non_bulk_mail', $form_state->getValue('non_bulk_mail'))
		     ->set('blk_header_name', $form_state->getValue('blk_header_name'))
		     ->set('blk_header_key', $form_state->getValue('blk_header_key'))
		     ->set('blk_log_api', $form_state->getValue('blk_log_api'))
		     ->save();

		parent::submitForm($form, $form_state);
	}
}