<?php


namespace Drupal\singpost_toolbox_find_postal_code\Form\Frontend;


use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\singpost_toolbox\Form\Frontend\FrontendFormBase;
use Drupal\singpost_toolbox\Helper\Recaptcha;
use Drupal\singpost_toolbox_find_postal_code\Helper\FindPostalCode;
use Exception;
use Drupal\singpost_protection\Utils\Protection;
use Drupal\Component\Render\FormattableMarkup;

/**
 * Class StreetForm
 *
 * @package Drupal\singpost_toolbox_find_postal_code\Form\Frontend
 */
class StreetForm extends FrontendFormBase{

	/**
	 * @return string
	 */
	public function getFormId(){
		return 'find_postal_code_frontend_form_street';
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

		$form['#attributes'] = [
			'class' => ['main-form toolbox-form frontend-find-postal-code-street ' . ($position == 'node' ? 'node-form' : 'side-form')]
		];

		$form['#action'] = Url::fromRoute('singpost.toolbox.find_postal_code.index')->toString();

		$form['#attributes']['id'] = 'fpc_frontend_form_street_' . $position;

		if($position == 'node'){

			$form['street']['fields'] = [
				'#type'       => 'container',
				'#attributes' => [
					'class' => [
						'track-trace-sec__tab-cont'
					]
				]
			];

			$form['street']['fields']['left'] = [
				'#type'       => 'container',
				'#attributes' => [
					'class' => [
						($position == 'node') ? 'track-trace-sec__post-text' : 'col-12'
					]
				]
			];

			$form['street']['fields']['right'] = [
				'#type'       => 'container',
				'#attributes' => [
					'class' => [
						($position == 'node') ? 'track-trace-sec__post-text' : 'col-12'
					]
				]
			];

			$form['street']['full'] = [
				'#type'       => 'container',
				'#attributes' => [
					'class' => [
						($position == 'node') ? '' : 'col-12'
					]
				]
			];

			$form['street']['fields']['left']['building_no'] = [
				'#title'         => t('Building / Block / House No.'),
				'#title_display' => ($position == 'node') ? 'before' : 'invisible',
				'#type'          => 'textfield',
				'#required'      => TRUE,
				'#required_error' => t('This field is required.'),
				'#placeholder'   => t('Eg: 100'),
				'#default_value' => $user_submissions['building_no'] ?? '',
				'#attributes' => [
					'class' => ['sgp-text-box__input']
				]
			];

			$form['street']['fields']['right']['street_name'] = [
				'#title'         => t('Street Name'),
				'#title_display' => ($position == 'node') ? 'before' : 'invisible',
				'#type'          => 'textfield',
				'#required'      => TRUE,
				'#required_error' => t('This field is required.'),
				'#placeholder'   => t('Eg: Woodlands Avenue 7'),
				'#default_value' => $user_submissions['street_name'] ?? '',
				'#attributes' => [
					'class' => ['sgp-text-box__input']
				]
			];

			$form['street']['full']['actions'] = ['#type' => 'actions'];

			$form['street']['full']['actions']['submit'] = [
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

			$form['street'] = [
				'#type'       => 'container',
				'#attributes' => [
					'class' => [
						'track-trace-sec__tab-cont'
					]
				]
			];

			$form['street']['fpc_tab'] = [
				'#type'          => 'select',
				'#required'      => TRUE,
				'#options'       => ['street' => 'Street', 'landmark' => 'Landmark', 'pobox' => 'PO Box'],
				'#default_value' => 'street',
				'#attributes' => [
					'class' => ['track-trace-sec__calculate-drop'],
					'id' 	=> ['street-side-form']
				]
			];

			$form['street']['building_no'] = [
				'#type'          => 'textfield',
				'#required'      => TRUE,
				'#required_error' => t('This field is required.'),
				'#placeholder'   => t('Building / Block / House No.'),
				'#default_value' => $user_submissions['building_no'] ?? '',
				'#attributes' => [
					'class' => ['sgp-text-box__input']
				]
			];

			$form['street']['street_name'] = [
				'#type'          => 'textfield',
				'#required'      => TRUE,
				'#required_error' => t('This field is required.'),
				'#placeholder'   => t('Street Name'),
				'#default_value' => $user_submissions['street_name'] ?? '',
				'#attributes' => [
					'class' => ['sgp-text-box__input']
				]
			];

			$form['street']['actions'] = ['#type' => 'actions'];

			$form['street']['actions']['submit'] = [
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
			$form['recaptcha']                       = [
				'#markup' => '<div class="modal fade" tabindex="-1" role="dialog" id="recaptcha-modal-fpc-street"><div class="modal-dialog modal-dialog-centered"><div class="modal-content recaptcha-modal"><div class="checkbox"><div id="recaptcha-str"></div></div></div></div></div>'
			];
			$form['g-recaptcha-response-fpc-street'] = [
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
		if (!$form_state->getValue('building_no')){
			$form_state->setErrorByName('building_no', t('Please enter Building/Block/House No.'));
		}

		$street_name = $form_state->getValue('street_name');

		if (!$street_name){
			$form_state->setErrorByName('street_name', t('Please enter Street Name.'));
		}elseif (strlen($street_name) < 2){
			$form_state->setErrorByName('street_name', t('Please input more than 2 characters.'));
		}

		if (class_exists('Protection')){
			try{
				$protection = new Protection('find_postal_code_street', ['READ_ONLY' => TRUE]);

				if ($protection->status == $protection::CAPTCHA){
					$recaptcha_token = $form_state->getValue('g-recaptcha-response-fpc-street');

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
		$pobox    = new POBoxForm();

		$session = $this->getRequest()->getSession();

		if ($session->get($landmark->getFormId())){
			$session->remove($landmark->getFormId());
		}

		if ($session->get($pobox->getFormId())){
			$session->remove($pobox->getFormId());
		}

		if (class_exists('Protection')){
			try{
				new Protection('find_postal_code_street');
			}catch (Exception $ex){
				$ex->getMessage();
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

			if (!empty($submission['building_no']) && !empty($submission['street_name'])){
				$data = $helper->getByStreet($submission['building_no'],
					$submission['street_name']);
			}

			return $data;
		}

		return - 1;
	}
}
