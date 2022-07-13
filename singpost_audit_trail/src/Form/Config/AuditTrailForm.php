<?php

namespace Drupal\singpost_audit_trail\Form\Config;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class AuditTrailForm
 *
 * @package Drupal\singpost_audit_trail\Form\Config
 */
class AuditTrailForm extends ConfigFormBase{

	public static $config_name = 'singpost.audit_trail';

	/**
	 * @inheritDoc
	 */
	public function getFormId(){
		return 'audit_trail_settings';
	}

	/**
	 * @inheritDoc
	 */
	public function buildForm(array $form, FormStateInterface $form_state){
		$setting = $this->config(self::$config_name);

		$form['delete_interval'] = [
			'#type'          => 'number',
			'#title'         => $this->t('Delete every (Days)'),
			'#description'   => 'Audit trail logs will be deleted by Drupal cron',
			'#required'      => TRUE,
			'#default_value' => $setting->get('delete_interval') ?? 180,
		];

		return parent::buildForm($form, $form_state);
	}

	/**
	 * @inheritDoc
	 */
	public function validateForm(array &$form, FormStateInterface $form_state){
		if (!is_numeric($form_state->getValue('delete_interval'))){
			$form_state->setErrorByName('delete_interval', t('Delete time must be a number'));
		}
	}

	/**
	 * @inheritDoc
	 */
	public function submitForm(array &$form, FormStateInterface $form_state){
		$this->config(self::$config_name)
		     ->set('delete_interval', $form_state->getValue('delete_interval'))
		     ->save();

		parent::submitForm($form, $form_state);
	}

	/**
	 * @inheritDoc
	 */
	protected function getEditableConfigNames(){
		return [self::$config_name];
	}
}