<?php


namespace Drupal\singpost_toolbox_redirect_redeliver\Form\Frontend;


use Drupal;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\singpost_toolbox\Form\Frontend\FrontendFormBase;
use Drupal\singpost_toolbox\Helper\Recaptcha;
use Drupal\singpost_toolbox_redirect_redeliver\Helper\RedirectRedeliver;
use Drupal\singpost_toolbox_redirect_redeliver\Model\Location;
use Exception;
use Drupal\singpost_protection\Utils\Protection;

/**
 * Class RedirectRedeliverForm
 *
 * @package Drupal\singpost_toolbox_redirect_redeliver\Form\Frontend
 */
class RedirectRedeliverForm extends FrontendFormBase{

	/**
	 * @return string
	 */
	public function getFormId(){
		return 'frontend_redirect_redeliver_form';
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 *
	 * @return array
	 */
	public function buildForm(array $form, FormStateInterface $form_state){
		$model     = new Location();
		$side_form = new RedirectRedeliverSideForm();
		$result    = $side_form->_getSubmission($side_form->getFormId());

		$form['#action'] = Url::fromRoute('singpost.toolbox.redirect_redeliver.index')->toString();

		$form['#attributes'] = [
			'class' => ['main-form toolbox-form frontend-redirect-redeliver node-form'],
		];

		$form['#attached'] = [
			'library' => [
				'singpost_base/datetimepicker',
				'singpost_toolbox/toolbox',
				'singpost_toolbox/recaptcha'
			],
		];

		for ($key = 1; $key <= 4; $key ++){
			$form['row-' . $key] = [
				'#type'       => 'container',
				'#attributes' => [
					'class' => ['row custom-border-bottom']
				]
			];

			if ($key < 4){
				$form['row-' . $key]['left'] = [
					'#type'       => 'container',
					'#attributes' => [
						'class' => ['col-lg-6 col-12']
					],
				];

				$form['row-' . $key]['right'] = [
					'#type'       => 'container',
					'#attributes' => [
						'class' => ['col-lg-6 col-12'],
					]
				];
			}else{
				$form['row-' . $key]['full'] = [
					'#type'       => 'container',
					'#attributes' => [
						'class' => ['col-12']
					]
				];
			}
		}

		$form['row-1']['left']['item_number'] = [
			'#type'          => 'textfield',
			'#default_value' => $result['item_number'] ?? '',
			'#title'         => $this->t('Article/Item Number'),
			'#placeholder'   => 'Eg. RR123412315SG',
			'#required'      => TRUE,
			'#attributes'    => [
				'id' => 'rr-item-number-node'
			]
		];

		$form['row-1']['right']['item_request_node'] = [
			'#type'          => 'select',
			'#options'       => [
				'redeliver' => 'Redeliver',
				'redirect'  => 'Redirect',
			],
			'#default_value' => $result['item_request'] ?? '',
			'#title'         => $this->t('Item Request'),
			'#required'      => TRUE,
			'#attributes'    => [
				'name' => 'item_request_node'
			]
		];

		$form['row-2']['left']['post_office_on_delivery'] = [
			'#type'        => 'select',
			'#options'     => $model->getLocations(),
			'#title'       => $this->t('Post Office on Delivery Advice'),
			'#required'    => TRUE,
			'#empty_value' => '',
		];

		$form['row-2']['right']['post_office_to_collect'] = [
			'#type'        => 'select',
			'#options'     => $model->getLocations(),
			'#title'       => $this->t('Post Office to Collect from'),
			'#empty_value' => '',
			'#states'      => [
				'visible'  => [
					':input[name="item_request_node"]' => [
						'value' => 'redirect',
					],
				],
				'required' => [
					':input[name="item_request_node"]' => [
						'value' => 'redirect',
					],
				],
			],
		];

		$form['row-2']['right']['date'] = [
			'#type'        => 'textfield',
			'#title'       => $this->t('Date for Next Delivery'),
			'#placeholder' => 'DD/MM/YYYY',
			'#attributes'  => [
				'class'    => ['calendar-only'],
				'readonly' => TRUE,
			],
			'#states'      => [
				'visible'  => [
					':input[name="item_request_node"]' => [
						'value' => 'redeliver',
					],
				],
				'required' => [
					':input[name="item_request_node"]' => [
						'value' => 'redeliver',
					],
				],
			],
		];

		$form['row-3']['left']['phone_number'] = [
			'#type'        => 'textfield',
			'#title'       => $this->t('Your Contact Number'),
			'#placeholder' => 'Your Contact Number',
		];

		$form['row-3']['right']['email'] = [
			'#type'        => 'email',
			'#title'       => $this->t('Your Email'),
			'#placeholder' => 'Your Email',
		];

		$form['row-4']['full'][] = [
			'help'                 => [
				'#type'   => 'markup',
				'#markup' => t("<div class='description'>As per guidance under the Personal Data Protection Act(PDPA) of Singapore, please provide us with your acknowledgement below. </div>"),
			],
			'terms_and_conditions' => [
				'#type'           => 'checkbox',
				'#title'          => $this->t('I acknowledge and accept the Privacy Policy and Website Terms of Use of SingPost Group.'),
				'#required'       => TRUE,
				'#required_error' => t('Please accept the Terms & Conditions.')
			],
			'note'                 => [
				'#type'           => 'checkbox',
				'#title'          => $this->t('Please note: We are only able to process your request after 12pm, the next working day from the date on the Delivery Advice'),
				'#required'       => TRUE,
				'#required_error' => t('Please accept the note.')
			]
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
					'btn btn-form-submit mt-4',
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
	 * @return \Drupal\Core\Form\FormStateInterface
	 */
	public function submitForm(array &$form, FormStateInterface $form_state){
		parent::submitForm($form, $form_state);

		$side_form = new RedirectRedeliverSideForm();
		$side_form->clearForm();

		$session = $this->getRequest()->getSession();

		$submission = $this->_getSubmission($this->getFormId());

		if (!empty($submission)){
			$helper     = new RedirectRedeliver();
			$is_success = FALSE;

			$item_no         = $submission['item_number'];
			$po_code         = $submission['post_office_on_delivery'];
			$po_tranfer_code = $submission['post_office_to_collect'];
			$contact_no      = $submission['phone_number'];
			$date            = $submission['date'];
			$email           = $submission['email'];
			$type            = $submission['item_request_node'];

			if (class_exists('Protection')){
				try{
					new Protection('redirect_redeliver');
				}catch (Exception $exception){

				}
			}

			if ($type == 'redirect'){
				if (!empty($item_no) && !empty($po_code) && !empty($po_tranfer_code) && !empty($contact_no) && !empty($email)){
					$is_success = $helper->getRedirect($item_no, $po_code,
						$po_tranfer_code, $contact_no, $email);
				}
			}else{
				if (!empty($item_no) && !empty($po_code) && !empty($date) && !empty($contact_no) && !empty($email)){
					$is_success = $helper->getRedeliver($item_no, $po_code, $date,
						$contact_no, $email);
				}
			}

			$session->set('data_is_success', $is_success);

			return $form_state->setRedirect('singpost.toolbox.redirect_redeliver.success');
		}
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 */
	public function validateForm(array &$form, FormStateInterface $form_state){
		$item_number             = $form_state->getValue('item_number');
		$item_request_node       = $form_state->getValue('item_request_node');
		$post_office_on_delivery = $form_state->getValue('post_office_on_delivery');
		$post_office_to_collect  = $form_state->getValue('post_office_to_collect');
		$date                    = $form_state->getValue('date');
		$phone_number            = $form_state->getValue('phone_number');
		$email                   = $form_state->getValue('email');
		$terms_and_conditions    = $form_state->getValue('terms_and_conditions');
		$note                    = $form_state->getValue('note');


		if (!$item_number || !preg_match('/^(\w{9,15}|(\w{21}))$/', $item_number)){
			$form_state->setErrorByName('item_number',
				t('Please enter your article/item number.'));
		}

		if (!$post_office_on_delivery){
			$form_state->setErrorByName('post_office_on_delivery',
				t('Please select post office on delivery advice.'));
		}

		if (!$item_request_node){
			$form_state->setErrorByName('item_request_node',
				t('Please enter your item request.'));
		}

		if ($item_request_node == 'redirect'){
			if (!$post_office_to_collect){
				$form_state->setErrorByName('item_request_node',
					t('Please select date for next delivery.'));
			}
		}else{
			if (!$date){
				$form_state->setErrorByName('item_request_node',
					t('Please select date for next delivery.'));
			}
		}

		if (!preg_match('/^\+?([0-9])([- 0-9])*(\d+)$/', $phone_number)){
			$form_state->setErrorByName('phone_number',
				t('Please enter your contact number.'));
		}

		if (!empty($email) && !Drupal::service('email.validator')->isValid($email)){
			$form_state->setErrorByName('email', t('Please input a valid email address'));
		}

		if (!$terms_and_conditions){
			$form_state->setErrorByName('terms_and_conditions',
				t('Please accept the Terms & Conditions.'));
		}

		if (!$note){
			$form_state->setErrorByName('note',
				t('Please accept the note.'));
		}

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

		if ($form_state::hasAnyErrors()){
			$this->clearForm();
		}
	}
}
