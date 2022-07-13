<?php


namespace Drupal\singpost_toolbox_find_postal_code\Form\Frontend;


use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\singpost_toolbox\Form\Frontend\FrontendFormBase;
use Drupal\singpost_toolbox\Helper\Recaptcha;
use Drupal\singpost_toolbox_find_postal_code\Helper\FindPostalCode;
use Exception;
use Drupal\singpost_protection\Utils\Protection;

/**
 * Class POBoxForm
 *
 * @package Drupal\singpost_toolbox_find_postal_code\Form\Frontend
 */
class POBoxForm extends FrontendFormBase{

	/**
	 * @return string
	 */
	public function getFormId(){
		return 'find_postal_code_frontend_form_pobox';
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 * @param string $position
	 *
	 * @return array
	 */
	public function buildForm(array $form, FormStateInterface $form_state, $position = 'node'){
		$user_submissions = ($position == 'node') ? $this->_getSubmission($this->getFormId()) : [];

		$helper = new FindPostalCode();

		$form['#attributes'] = [
			'class' => ['main-form toolbox-form frontend-find-postal-code-pobox ' . ($position == 'node' ? 'node-form' : 'side-form')]
		];

		$form['#attributes']['id'] = 'fpc_frontend_form_pobox_' . $position;

		$form['#action'] = Url::fromRoute('singpost.toolbox.find_postal_code.index')->toString();

		if($position == 'node'){

			$form['row'] = [
				'#type'       => 'container',
				'#attributes' => [
					'class' => [
						'123'
					]
				]
			];

			$form['row']['full'] = [
				'#type'       => 'container',
				'#attributes' => [
					'class' => [
						($position == 'node') ? 'col-12' : 'd-none'
					]
				]
			];

			$form['row']['wrap_fields'] = [
				'#type'       => 'container',
				'#attributes' => [
					'class' => [
						'track-trace-sec__tab-cont track-trace-sec__tab-cont--width'
					]
				]
			];

			$form['row']['wrap_fields']['left'] = [
				'#type'       => 'container',
				'#attributes' => [
					'class' => [
						($position == 'node') ? 'track-trace-sec__post-text' : 'col-12'
					]
				]
			];

			$form['row']['wrap_fields']['right'] = [
				'#type'       => 'container',
				'#attributes' => [
					'class' => [
						($position == 'node') ? 'track-trace-sec__post-text' : 'col-12'
					]
				]
			];

			$form['row']['wrap_fields']['po_wrapper'] = [
				'#type'       => 'container',
				'#attributes' => [
					'class' => [
						($position == 'node') ? 'track-trace-sec__post-text' : 'col-12'
					]
				]
			];

			$form['row']['find'] = [
				'#type'       => 'container',
				'#attributes' => [
					'class' => [
						($position == 'node') ? '' : 'col-12'
					]
				]
			];
			/*
			$form['row']['full']['po_box_type_title'] = [
				'#type'   => 'markup',
				'#markup' => t("<div class='font-weight-bold label'>PO Box/Locked Bag No.</div>"),
			]; */

			$form['row']['wrap_fields']['left']['po_box_type'] = [
				'#title'         => t('Select Type'),
				'#type'          => 'select',
				//'#title_display' => 'invisible',
				'#required'      => TRUE,
				'#options'       => $helper::TYPE,
				'#default_value' => $user_submissions['po_box_type'] ?? $helper::BOX,
				'#attributes'    => [
					'class' => ['form-text track-trace-sec__calculate-drop po-box-type' . ($position != 'node' ? '-side' : '')]
				]
			];

			$form['row']['wrap_fields']['right']['delivery_no'] = [
				'#type'          => 'textfield',
				'#title'         => t('PO Box / Locked Bag No.'),
				//'#title_display' => 'invisible',
				'#required'      => TRUE,
				'#required_error' => t('This field is required.'),
				'#placeholder'   => t('Eg: 5'),
				'#default_value' => $user_submissions['delivery_no'] ?? '',
				'#attributes'    => [
					'class' => ['sgp-text-box__input delivery-no' . ($position != 'node' ? '-side' : '')]
				]
			];

			$form['row']['wrap_fields']['po_wrapper']['post_office'] = [
				'#type'          => 'textfield',
				'#required'      => TRUE,
				'#required_error' => t('This field is required.'),
				'#title'         => t('Post Office Location'),
				'#title_display' => ($position == 'node') ? 'before' : 'invisible',
				'#placeholder'   => t('Eg: Toa Payoh'),
				'#default_value' => $user_submissions['post_office'] ?? '',
				'#attributes'    => [
					'class' => ['sgp-text-box__input']
				]
			];

			$form['row']['find']['actions'] = ['#type' => 'actions'];

			$form['row']['find']['actions']['submit'] = [
				'#type'       => 'submit',
				'#value'      => t('Search Now'),
				'#attributes' => [
					'class' => [
						'btn btn-form-submit sgp-link-btn sgp-link-btn--box span-wrapper'
					]
				]
			];
		}
		else{

			$form['row'] = [
				'#type'       => 'container',
				'#attributes' => [
					'class' => [
						'track-trace-sec__tab-cont'
					]
				]
			];

			$form['row']['fpc_tab'] = [
				'#type'          => 'select',
				'#required'      => TRUE,
				'#options'       => ['street' => 'Street', 'landmark' => 'Landmark', 'pobox' => 'PO Box'],
				'#default_value' => 'pobox',
				'#attributes' => [
					'class' => ['track-trace-sec__calculate-drop'],
					'id' 	=> ['pobox-side-form']
				]
			];

			$form['row']['po_box_type'] = [
				'#type'          => 'select',
				'#required'      => TRUE,
				'#options'       => $helper::TYPE,
				'#default_value' => $user_submissions['po_box_type'] ?? $helper::BOX,
				'#attributes'    => [
					'class' => ['track-trace-sec__calculate-drop']
				]
			];

			$form['row']['delivery_no'] = [
				'#type'          => 'textfield',
				'#required'      => TRUE,
				'#required_error' => t('This field is required.'),
				'#placeholder'   => t('PO Box No.'),
				'#default_value' => $user_submissions['delivery_no'] ?? '',
				'#attributes'    => [
					'class' => ['track-trace-sec__input-text']
				]
			];

			$form['row']['post_office'] = [
				'#type'          => 'textfield',
				'#required'      => TRUE,
				'#required_error' => t('This field is required.'),
				'#placeholder'   => t('Post Office'),
				'#default_value' => $user_submissions['post_office'] ?? '',
				'#attributes'    => [
					'class' => ['track-trace-sec__input-text']
				]
			];

			$form['row']['actions'] = ['#type' => 'actions'];

			$form['row']['actions']['submit'] = [
				'#type'       => 'submit',
				'#value'      => t('Find'),
				'#attributes' => [
					'class' => [
						'btn btn-form-submit sgp-link-btn sgp-link-btn--box span-wrapper'
					]
				]
			];

		}

		if ($this->config_recaptcha->get('site_key')){
			$form['recaptcha']                      = [
				'#markup' => '<div class="modal fade" tabindex="-1" role="dialog" id="recaptcha-modal-fpc-pobox"><div class="modal-dialog modal-dialog-centered"><div class="modal-content recaptcha-modal"><div class="checkbox"><div id="recaptcha-po"></div></div></div></div></div>'
			];
			$form['g-recaptcha-response-fpc-pobox'] = [
				'#type'           => 'textarea',
				'#attributes'     => ['class' => ['d-none']],
				'#theme_wrappers' => [],
			];
		}

		return $form;
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 */
	public function validateForm(array &$form, FormStateInterface $form_state){
		$helper = new FindPostalCode();
		$type   = $form_state->getValue('po_box_type');

		if (!$type){
			$form_state->setErrorByName('po_box_type', t('Please choose Type.'));
		}

		if (!$form_state->getValue('delivery_no')){
			if ($type == $helper::BOX){
				$form_state->setErrorByName('delivery_no', t('Please enter PO Box No.'));
			}else{
				$form_state->setErrorByName('delivery_no',
					t('Please enter Locked Bag Service No.'));
			}
		}

		$post_office = $form_state->getValue('post_office');

		if (!$post_office){
			$form_state->setErrorByName('post_office', t('Please enter Post Office.'));
		}elseif (strlen($post_office) < 2){
			$form_state->setErrorByName('post_office', t('Please input more than 2 characters.'));
		}

		if (class_exists('Protection')){
			try{
				$protection = new Protection('find_postal_code_pobox', ['READ_ONLY' => TRUE]);

				if ($protection->status == $protection::CAPTCHA){
					$recaptcha_token = $form_state->getValue('g-recaptcha-response-fpc-pobox');

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
						t('You are not allow to track the item, please contact our customer service for more support.'));
				}

			}catch (Exception $exception){
			}
		}

		if ($form_state::hasAnyErrors()){
			$this->clearForm();
		}
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 *
	 * @return bool|void
	 */
	public function submitForm(array &$form, FormStateInterface $form_state){
		parent::submitForm($form, $form_state);

		$landmark = new LandmarkForm();
		$street   = new StreetForm();

		$session = $this->getRequest()->getSession();

		if ($session->get($landmark->getFormId())){
			$session->remove($landmark->getFormId());
		}

		if ($session->get($street->getFormId())){
			$session->remove($street->getFormId());
		}

		if (class_exists('Protection')){
			try{
				new Protection('find_postal_code_pobox');
			}catch (Exception $ex){
			}
		}

		$form_state->setRedirect('singpost.toolbox.find_postal_code.index');
	}

	/**
	 * @return array|int|mixed|\SimpleXMLElement|string
	 */
	public function getResults(){
		$submission = $this->_getSubmission($this->getFormId());

		if ($submission){
			$helper = new FindPostalCode();
			$data   = [];

			if (!empty($submission['po_box_type']) && !empty($submission['delivery_no']) && !empty($submission['post_office'])){
				$data = $helper->getByPOBox($submission['po_box_type'],
					$submission['delivery_no'], $submission['post_office']);
			}


			return $data;
		}

		return - 1;
	}
}
