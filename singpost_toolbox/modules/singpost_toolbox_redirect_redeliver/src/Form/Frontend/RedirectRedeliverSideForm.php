<?php

namespace Drupal\singpost_toolbox_redirect_redeliver\Form\Frontend;

use Drupal\Core\Form\FormStateInterface;
use Drupal\singpost_toolbox\Form\Frontend\FrontendFormBase;
use Drupal\singpost_toolbox\Helper\Recaptcha;
use Exception;
use Drupal\singpost_protection\Utils\Protection;

/**
 * Class RedirectRedeliverSideForm
 *
 * @package Drupal\singpost_toolbox_redirect_redeliver\Form\Frontend
 */
class RedirectRedeliverSideForm extends FrontendFormBase{

	/**
	 * @return string
	 */
	public function getFormId(){
		return 'frontend_redirect_redeliver_side_form';
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 *
	 * @return array
	 */
	public function buildForm(
		array $form,
		FormStateInterface $form_state){

		$form['#attributes'] = [
			'class' => ['main-form toolbox-form frontend-redirect-redeliver side-form'],
		];

		$form['#attached'] = [
			'library' => [
				'singpost_toolbox/toolbox',
				'singpost_toolbox/recaptcha',
			],
		];

		$form['item_number'] = [
			'#type'          => 'textfield',
			'#title_display' => 'invisible',
			'#title'         => $this->t('Article/Item Number'),
			'#placeholder'   => 'Eg. RR123412315SG',
			'#required'      => TRUE,
			'#attributes'    => [
				'id' => 'rr-item-number-side'
			]
		];

		$form['item_request'] = [
			'#type'          => 'select',
			'#title_display' => 'invisible',
			'#options'       => [
				'redeliver' => 'Redeliver',
				'redirect'  => 'Redirect',
			],
			'#title'         => $this->t('Item Request'),
			'#required'      => TRUE
		];

		$form['actions'] = [
			'#type'       => 'actions',
			'#attributes' => [
				'class' => ['text-right'],
			],
		];

		$form['actions']['submit'] = [
			'#type'       => 'submit',
			'#value'      => t('Send Request'),
			'#attributes' => [
				'class' => [
					'btn btn-form-submit',
				],
			],
		];

		if ($this->config_recaptcha->get('site_key')){
			$form['recaptcha']                     = [
				'#markup' => '<div class="modal fade" tabindex="-1" role="dialog" id="recaptcha-modal-redirect"><div class="modal-dialog modal-dialog-centered"><div class="modal-content recaptcha-modal"><div class="checkbox"><div id="recaptcha-redirect"></div></div></div></div></div>'
			];
			$form['g-recaptcha-response-redirect'] = [
				'#type'           => 'textarea',
				'#attributes'     => [
					'class' => ['d-none']
				],
				'#theme_wrappers' => [],
			];
		}

		return $form;
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 *
	 * @return void
	 */
	public function submitForm(array &$form, FormStateInterface $form_state){
		parent::submitForm($form, $form_state);

		if (class_exists('Protection')){
			try{
				new Protection('redirect_redeliver');
			}catch (Exception $exception){

			}
		}

		$form_state->setRedirect('singpost.toolbox.redirect_redeliver.index');
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 */
	public function validateForm(array &$form, FormStateInterface $form_state){
		parent::validateForm($form, $form_state);

		if (class_exists('Protection')){
			try{
				$protection = new Protection('redirect_redeliver', ['READ_ONLY' => TRUE]);

				if ($protection->status == $protection::CAPTCHA){
					$recaptcha_token = $form_state->getValue('g-recaptcha-response-redirect');

					$site_key   = $this->config_recaptcha->get('site_key');
					$secret_key = $this->config_recaptcha->get('secret_key');

					if ($site_key && $secret_key){
						$recaptcha          = new Recaptcha($site_key, $secret_key);
						$recaptcha_response = $recaptcha->verifyResponse($recaptcha_token);

						if (isset($recaptcha_response['success']) && !$recaptcha_response['success']){
							$error_msg = is_array($recaptcha_response['error-codes']) ? $recaptcha_response['error-codes'][0] : $recaptcha_response['error-codes'];

							$form_state->setErrorByName('error',
								t('Error: ' . $error_msg));
						}
					}
				}

				if ($protection->status == $protection::BLACKLIST){
					$form_state->setErrorByName('error',
						t('You are not allow to track the item, please contact our customer service for more support."'));
				}

			}catch (Exception $exception){
			}
		}
	}
}
