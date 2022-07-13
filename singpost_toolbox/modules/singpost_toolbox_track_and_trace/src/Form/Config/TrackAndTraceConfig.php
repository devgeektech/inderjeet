<?php


namespace Drupal\singpost_toolbox_track_and_trace\Form\Config;


use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class TrackAndTraceConfig
 *
 * @package Drupal\singpost_toolbox_track_and_trace\Form\Config
 */
class TrackAndTraceConfig extends ConfigFormBase{

	public static $config_name = 'track_and_trace.config';

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
		return 'track_and_trace_config_form';
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

		$form['api']['tnt_system_id'] = [
			'#type'          => 'textfield',
			'#required'      => TRUE,
			'#title'         => $this->t('System ID'),
			'#default_value' => $setting->get('tnt_system_id') ?? '',
		];

		$form['api']['tnt_url_api'] = [
			'#type'          => 'url',
			'#required'      => TRUE,
			'#title'         => $this->t('Url API'),
			'#default_value' => $setting->get('tnt_url_api') ?? '',
		];

		$form['tnt_local_country'] = [
			'#type'  => 'details',
			'#title' => t('Local country')
		];

		$form['tnt_local_country']['local_name'] = [
			'#type'          => 'textfield',
			'#title'         => $this->t('Name'),
			'#default_value' => $setting->get('tnt_local_country')['name'] ?? '',
		];

		$form['tnt_oversea_country'] = [
			'#type'   => 'fieldset',
			'#title'  => 'Overseas Country',
			'#prefix' => '<div id="dc-row-wrapper">',
			'#suffix' => '</div>',
			'#tree'   => TRUE
		];

		$oversea_country = $setting->get('tnt_oversea_country');

		if ($oversea_country && !empty($oversea_country)){
			$content = array_keys($oversea_country);
		}

		if (empty($form_state->get('fields'))){
			$form_state->set('fields', (!empty($content)) ? $content : [1]);
		}

		$fields = $form_state->get('fields');

		foreach ($fields as $key => $value){
			$form['tnt_oversea_country'][$value] = [
				'#type'  => 'details',
				'#title' => t('Country @num', ['@num' => $key + 1]),
				'#open'  => FALSE,
				'#tree'  => TRUE
			];

			$form['tnt_oversea_country'][$value]['name'] = [
				'#type'          => 'textfield',
				'#title'         => t('Country name'),
				'#placeholder'   => 'Name',
				'#default_value' => $oversea_country[$value]['name'] ?? ''
			];

			$form['tnt_oversea_country'][$value]['status'] = [
				'#type'          => 'textfield',
				'#title'         => t('Status'),
				'#placeholder'   => 'Status',
				'#default_value' => $oversea_country[$value]['status'] ?? ''
			];

			$form['tnt_oversea_country'][$value]['content'] = [
				'#type'          => 'textarea',
				'#title'         => t('Content'),
				'#cols'          => 5,
				'#placeholder'   => 'Content',
				'#default_value' => $oversea_country[$value]['content'] ?? ''
			];

			if ($value > 1){
				$form['tnt_oversea_country'][$value]['actions'] = ['#type' => 'actions'];

				$form['tnt_oversea_country'][$value]['actions']['remove_row'] = [
					'#type'                    => 'submit',
					'#name'                    => 'remove_row_' . $value,
					'#value'                   => t('Remove'),
					'#submit'                  => ['::remove'],
					'#limit_validation_errors' => [],
					'#ajax'                    => [
						'callback' => '::callback',
						'wrapper'  => 'dc-row-wrapper',
					]
				];
			}
		}

		$form['tnt_oversea_country']['actions'] = ['#type' => 'actions'];

		$form['tnt_oversea_country']['actions']['add_row'] = [
			'#type'                    => 'submit',
			'#value'                   => t('Add'),
			'#submit'                  => ['::add'],
			'#limit_validation_errors' => [],
			'#ajax'                    => [
				'callback' => '::callback',
				'wrapper'  => 'dc-row-wrapper',
			],
		];

		$form['tnt_local_country']['local_content'] = [
			'#type'          => 'textarea',
			'#cols'          => 5,
			'#title'         => $this->t('Content'),
			'#default_value' => $setting->get('tnt_local_country')['content'] ?? '',
		];

		$form['message'] = [
			'#type'  => 'details',
			'#title' => t('Message')
		];

		$form['message']['tnt_other_note'] = [
			'#type'          => 'text_format',
			'#format'        => $setting->get('tnt_other_note')['format'] ?? 'basic_html',
			'#title'         => $this->t('Other Notes'),
			'#default_value' => $setting->get('tnt_other_note')['value'] ?? '',
		];

		$form['message']['tnt_notfound_tracking_message'] = [
			'#type'          => 'text_format',
			'#format'        => $setting->get('tnt_notfound_tracking_message')['format'] ?? 'basic_html',
			'#title'         => $this->t('Not Found Tracking Message'),
			'#default_value' => $setting->get('tnt_notfound_tracking_message')['value'] ?? '',
		];

		$form['message']['tnt_redirect_message'] = [
			'#type'          => 'textarea',
			'#cols'          => 5,
			'#title'         => $this->t('Tooltip Redirect/Redeliver Message'),
			'#default_value' => $setting->get('tnt_redirect_message') ?? '',
		];

		$form['message']['tnt_error_tracking_message'] = [
			'#type'          => 'textarea',
			'#cols'          => 5,
			'#title'         => $this->t('Error Tracking Message'),
			'#default_value' => $setting->get('tnt_error_tracking_message') ?? '',
		];

		$form['message']['tnt_error_message'] = [
			'#type'          => 'textarea',
			'#cols'          => 5,
			'#title'         => $this->t('Error Message'),
			'#default_value' => $setting->get('tnt_error_message') ?? '',
		];

		return parent::buildForm($form, $form_state);
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 */
	public function add(array &$form, FormStateInterface $form_state){
		$fields = $form_state->get('fields');

		if (count($fields) > 0){
			$fields[] = max($fields) + 1;
		}else{
			$fields[] = 0;
		}

		$form_state->set('fields', $fields);
		$form_state->setRebuild();
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 */
	public function remove(array &$form, FormStateInterface $form_state){
		$field_remove = $form_state->getTriggeringElement()['#parents'][1];
		$fields       = $form_state->get('fields');
		$key_remove   = array_search($field_remove, $fields);

		unset($fields[$key_remove]);
		$form_state->set('fields', $fields);
		$form_state->setRebuild();
	}

	/**
	 * @param array $form
	 *
	 * @return mixed
	 */
	public function callback(array &$form){
		return $form['tnt_oversea_country'];
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 */
	public function submitForm(array &$form, FormStateInterface $form_state){
		$oversea_country = $form_state->getValue('tnt_oversea_country');
		unset($oversea_country['actions']);

		$local_name    = $form_state->getValue('local_name');
		$local_content = $form_state->getValue('local_content');
		$local_country = [
			'name'    => $local_name,
			'content' => $local_content
		];

		$this->config(self::$config_name)
		     ->set('tnt_system_id', $form_state->getValue('tnt_system_id'))
		     ->set('tnt_url_api', $form_state->getValue('tnt_url_api'))
		     ->set('tnt_oversea_country', $oversea_country)
		     ->set('tnt_local_country', $local_country)
		     ->set('tnt_other_note', $form_state->getValue('tnt_other_note'))
			 ->set('tnt_notfound_tracking_message',
			     $form_state->getValue('tnt_notfound_tracking_message'))
		     ->set('tnt_redirect_message', $form_state->getValue('tnt_redirect_message'))
		     ->set('tnt_error_tracking_message',
			     $form_state->getValue('tnt_error_tracking_message'))
		     ->set('tnt_error_message', $form_state->getValue('tnt_error_message'))
		     ->save();

		parent::submitForm($form, $form_state);
	}
}