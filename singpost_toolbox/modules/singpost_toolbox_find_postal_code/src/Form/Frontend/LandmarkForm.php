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
 * Class LandmarkForm
 *
 * @package Drupal\singpost_toolbox_find_postal_code\Form\Frontend
 */
class LandmarkForm extends FrontendFormBase{

	/**
	 * @return string
	 */
	public function getFormId(){
		return 'find_postal_code_frontend_form_landmark';
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
			'class' => ['main-form toolbox-form frontend-find-postal-code-landmark ' . ($position == 'node' ? 'node-form' : 'side-form')]
		];

		$form['#attributes']['id'] = 'fpc_frontend_form_landmark_' . $position;

		$form['#action'] = Url::fromRoute('singpost.toolbox.find_postal_code.index')->toString();

		if($position == 'node'){

			$form['landmark'] = [
				'#type'       => 'container',
				'#attributes' => [
					'class' => [
						'row'
					]
				]
			];

			$form['row'] = [
				'#type'       => 'container',
				'#attributes' => [
					'class' => [
						'row'
					]
				]
			];

			$form['landmark']['half_left'] = [
				'#type'       => 'container',
				'#attributes' => [
					'class' => [
						($position == 'node') ? 'col-lg-6 col-12' : 'col-12'
					]
				]
			];

			$form['row']['half_left'] = [
				'#type'       => 'container',
				'#attributes' => [
					'class' => [
						($position == 'node') ? 'col-lg-2 col-md-4' : 'col-12'
					]
				]
			];

			$form['landmark']['half_left']['major_building'] = [
				'#title'         => t('Major Building / Estate Name'),
				'#title_display' => ($position == 'node') ? 'before' : 'invisible',
				'#type'          => 'textfield',
				'#required'      => TRUE,
				'#required_error' => t('This field is required.'),
				'#placeholder'   => t('Eg: Paya Lebar Square'),
				'#default_value' => $user_submissions['major_building'] ?? '',
				'#attributes' => [
					'class' => ['sgp-text-box__input']
				]
			];

			$form['row']['half_left']['actions'] = ['#type' => 'actions'];

			$form['row']['half_left']['actions']['submit'] = [
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

			$form['landmark'] = [
				'#type'       => 'container',
				'#attributes' => [
					'class' => [
						'track-trace-sec__tab-cont'
					]
				]
			];

			$form['landmark']['fpc_tab'] = [
				'#type'          => 'select',
				'#required'      => TRUE,
				'#options'       => ['street' => 'Street', 'landmark' => 'Landmark', 'pobox' => 'PO Box'],
				'#default_value' => 'landmark',
				'#attributes' => [
					'class' => ['track-trace-sec__calculate-drop'],
					'id' 	=> ['landmark-side-form']
				]
			];

			$form['landmark']['major_building'] = [
				'#type'          => 'textfield',
				'#required'      => TRUE,
				'#required_error' => t('This field is required.'),
				'#placeholder'   => t('Major Building / Estate Name'),
				'#default_value' => $user_submissions['major_building'] ?? '',
				'#attributes' => [
					'class' => ['track-trace-sec__input-text']
				]
			];

			$form['landmark']['actions'] = ['#type' => 'actions'];

			$form['landmark']['actions']['submit'] = [
				'#type'       => 'submit',
				'#value'      => t('Find'),
				'#attributes' => [
					'class' => [
						'sgp-link-btn sgp-link-btn--box span-wrapper'
					]
				]
			];
		}

		if ($this->config_recaptcha->get('site_key')){
			$form['recaptcha']                         = [
				'#markup' => '<div class="modal fade" tabindex="-1" role="dialog" id="recaptcha-modal-fpc-landmark"><div class="modal-dialog modal-dialog-centered"><div class="modal-content recaptcha-modal"><div class="checkbox"><div id="recaptcha-lm"></div></div></div></div></div>'
			];
			$form['g-recaptcha-response-fpc-landmark'] = [
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
		$building = $form_state->getValue('major_building');

		if (!$building){
			$form_state->setErrorByName('major_building',
				t('Please enter Major Building / Estate Name.'));
		}elseif (strlen($building) < 2){
			$form_state->setErrorByName('major_building',
				t('Please input more than 2 characters.'));
		}

		if (class_exists('Protection')){
			try{
				$protection = new Protection('find_postal_code_landmark', ['READ_ONLY' => TRUE]);

				if ($protection->status == $protection::CAPTCHA){
					$recaptcha_token = $form_state->getValue('g-recaptcha-response-fpc-landmark');

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

		$street = new StreetForm();
		$pobox  = new POBoxForm();

		$session = $this->getRequest()->getSession();

		if ($session->get($street->getFormId())){
			$session->remove($street->getFormId());
		}

		if ($session->get($pobox->getFormId())){
			$session->remove($pobox->getFormId());
		}

		if (class_exists('Protection')){
			try{
				new Protection('find_postal_code_landmark');
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

			if (!empty($submission['major_building'])){
				$data = $helper->getByLandmark($submission['major_building']);
			}

			return $data;
		}

		return - 1;
	}
}