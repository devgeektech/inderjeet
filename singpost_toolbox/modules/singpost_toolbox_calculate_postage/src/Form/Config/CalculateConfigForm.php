<?php


namespace Drupal\singpost_toolbox_calculate_postage\Form\Config;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\singpost_base\Support\EntityUrlFieldHelper;

/**
 * Class CalculateConfigForm
 *
 * @package Drupal\singpost_toolbox_calculate_postage\Form\Config
 */
class CalculateConfigForm extends ConfigFormBase{

	/**
	 * @var string
	 */
	public static $config_name = 'calculate.config';

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
		return 'calculate_config_form';
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 *
	 * @return array
	 * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
	 * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
	 */
	public function buildForm(array $form, FormStateInterface $form_state){
		$setting = $this->config(self::$config_name);

		$form['setting_api'] = [
			'#type'  => 'details',
			'#title' => $this->t('Setting API'),
		];

		$form['setting_api']['oversea_url'] = [
			'#type'          => 'url',
			'#title'         => $this->t('Oversea URL'),
			'#default_value' => $setting->get('oversea_url') ?? '',
			'#required'      => TRUE,
		];

		$form['setting_api']['singapore_url'] = [
			'#type'          => 'url',
			'#title'         => $this->t('Singapore URL'),
			'#default_value' => $setting->get('singapore_url') ?? '',
			'#required'      => TRUE,
		];

		$form['setting_api']['sr_error_message'] = [
			'#type'          => 'textarea',
			'#cols'          => 5,
			'#title'         => $this->t('Error Message'),
			'#default_value' => $setting->get('sr_error_message') ?? ''
		];

		$form['authorize'] = [
			'#type'  => 'details',
			'#title' => t('Authorize')
		];

		$form['authorize']['cal_authorize_header_name'] = [
			'#type'          => 'textfield',
			'#title'         => $this->t('Authorize Header Name'),
			'#default_value' => $setting->get('cal_authorize_header_name') ?? 'Authorization',
		];

		$form['authorize']['cal_authorize_header_key'] = [
			'#type'          => 'textfield',
			'#title'         => $this->t('Authorize Header Key'),
			'#default_value' => $setting->get('cal_authorize_header_key') ?? ''
		];

		$form['advice_of_receipt'] = [
			'#type'          => 'text_format',
			'#title'         => $this->t('Tooltip – Advice of Receipt'),
			'#format'        => $setting->get('advice_of_receipt')['format'] ?? 'full_html',
			'#default_value' => $setting->get('advice_of_receipt')['value'] ?? ''
		];

		$form['compensation'] = [
			'#type'  => 'details',
			'#title' => $this->t('Tooltip - Compensation'),
		];

		$form['compensation']['default'] = [
			'#type'          => 'text_format',
			'#title'         => $this->t('Tooltip – Compensation for Default'),
			'#format'        => $setting->get('default')['format'] ?? 'full_html',
			'#default_value' => $setting->get('default')['value'] ?? ''
		];

		$form['compensation']['registered'] = [
			'#type'          => 'text_format',
			'#title'         => $this->t('Tooltip – Compensation for Registered'),
			'#format'        => $setting->get('registered')['format'] ?? 'full_html',
			'#default_value' => $setting->get('registered')['value'] ?? ''
		];

		$form['compensation']['smartpac'] = [
			'#type'          => 'text_format',
			'#title'         => $this->t('Tooltip – Compensation for Smartpac'),
			'#format'        => $setting->get('smartpac')['format'] ?? 'full_html',
			'#default_value' => $setting->get('smartpac')['value'] ?? ''
		];

		$form['compensation']['am_mail'] = [
			'#type'          => 'text_format',
			'#title'         => $this->t('Tooltip – Compensation for A.M Mail'),
			'#format'        => $setting->get('am_mail')['format'] ?? 'full_html',
			'#default_value' => $setting->get('am_mail')['value'] ?? ''
		];

		$form['tracked_package'] = [
			'#type'          => 'text_format',
			'#title'         => $this->t('Tooltip – Tracked Package'),
			'#format'        => $setting->get('tracked_package')['format'] ?? 'full_html',
			'#default_value' => $setting->get('tracked_package')['value'] ?? ''
		];

		$form['doorstep_pickup'] = [
			'#type'          => 'text_format',
			'#title'         => $this->t('Tooltip – Doorstep Pickup'),
			'#format'        => $setting->get('doorstep_pickup')['format'] ?? 'full_html',
			'#default_value' => $setting->get('doorstep_pickup')['value'] ?? ''
		];

		$form['speedpost_express'] = [
			'#type'  => 'details',
			'#title' => $this->t('Speedpost Express'),
		];

		$form['speedpost_express']['minimum_price'] = [
			'#type'          => 'text_format',
			'#title'         => $this->t('Speedpost Express – Minimum Price'),
			'#format'        => $setting->get('minimum_price')['format'] ?? 'full_html',
			'#default_value' => $setting->get('minimum_price')['value'] ?? ''
		];

		$form['speedpost_express']['book_now_tooltip'] = [
			'#type'          => 'text_format',
			'#title'         => $this->t('Speedpost Express – Book now tooltip'),
			'#format'        => $setting->get('book_now_tooltip')['format'] ?? 'full_html',
			'#default_value' => $setting->get('book_now_tooltip')['value'] ?? ''
		];

		$form['book_now_url'] = [
			'#type'          => 'url',
			'#title'         => $this->t('Book now URL'),
			'#default_value' => $setting->get('book_now_url') ?? '',
		];

		$form['buy_now_url'] = [
			'#type'          => 'url',
			'#title'         => $this->t('Buy now URL Poly M'),
			'#default_value' => $setting->get('buy_now_url') ?? '',
		];

		$form['buy_now_url_sp'] = [
			'#type'          => 'url',
			'#title'         => $this->t('Buy now URL SmartPac'),
			'#default_value' => $setting->get('buy_now_url_sp') ?? '',
		];

		$form['receive_url'] = [
			'#type'                  => 'entity_autocomplete',
			'#target_type'           => 'node',
			'#required'              => TRUE,
			'#attributes'            => ['data-autocomplete-first-character-blacklist' => '/#?'],
			'#process_default_value' => FALSE,
			'#title'                 => t('Receive Page'),
			'#description'           => t('Start typing the title of a piece of content to select it. You can also enter an internal path such as %add-node or an external URL such as %url. Enter %front to link to the front page.',
				[
					'%front'    => '<front>',
					'%add-node' => '/node/add',
					'%url'      => 'http://example.com'
				]),
			'#default_value'         => ($setting->get('receive_url')) ? EntityUrlFieldHelper::getUriAsDisplayableString($setting->get('receive_url')) : '',
			'#element_validate'      => ['Drupal\singpost_base\Support\EntityUrlFieldHelper::validateUriElement'],
		];

		$form['delivery_url'] = [
			'#type'          => 'textarea',
			'#title'         => $this->t('See Delivery Times & Rates (Mail/Postal)'),
			'#default_value' => $setting->get('delivery_url') ?? ''
		];

		$form['note_url'] = [
			'#type'          => 'textarea',
			'#title'         => $this->t('What can\'t I send?'),
			'#default_value' => $setting->get('note_url') ?? ''
		];

		return parent::buildForm($form, $form_state);
	}

	/**
	 * @inheritDoc
	 */
	public function submitForm(array &$form, FormStateInterface $form_state){
		if (!empty($form_state->getValue('receive_url'))){
			$url = EntityUrlFieldHelper::getUserEnteredStringAsUri($form_state->getValue('receive_url'));
		}

		$this->config(self::$config_name)
			->set('oversea_url', $form_state->getValue('oversea_url'))
			->set('singapore_url', $form_state->getValue('singapore_url'))
			->set('sr_error_message', $form_state->getValue('sr_error_message'))
			->set('cal_authorize_header_name', $form_state->getValue('cal_authorize_header_name'))
			->set('cal_authorize_header_key', $form_state->getValue('cal_authorize_header_key'))
			->set('advice_of_receipt', $form_state->getValue('advice_of_receipt'))
			->set('default', $form_state->getValue('default'))
			->set('registered', $form_state->getValue('registered'))
			->set('smartpac', $form_state->getValue('smartpac'))
			->set('am_mail', $form_state->getValue('am_mail'))
			->set('tracked_package', $form_state->getValue('tracked_package'))
			->set('doorstep_pickup', $form_state->getValue('doorstep_pickup'))
			->set('minimum_price', $form_state->getValue('minimum_price'))
			->set('book_now_tooltip', $form_state->getValue('book_now_tooltip'))
			->set('book_now_url', $form_state->getValue('book_now_url'))
			->set('buy_now_url', $form_state->getValue('buy_now_url'))
			->set('buy_now_url_sp', $form_state->getValue('buy_now_url_sp'))
			->set('receive_url', ($url ?? ''))
			->set('delivery_url', $form_state->getValue('delivery_url'))
			->set('note_url', $form_state->getValue('note_url'))
			->save();

		parent::submitForm($form, $form_state);
	}

}