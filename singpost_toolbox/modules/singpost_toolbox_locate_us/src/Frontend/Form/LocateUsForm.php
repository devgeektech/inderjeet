<?php


namespace Drupal\singpost_toolbox_locate_us\Frontend\Form;


use Drupal\Core\Form\FormStateInterface;
use Drupal\singpost_toolbox\Form\Frontend\FrontendFormBase;
use Drupal\singpost_toolbox\Helper\Recaptcha;
use Drupal\singpost_toolbox_locate_us\Helper\LocateUs;
use Drupal\singpost_toolbox_locate_us\Model\LocateUsType;
use Exception;
use Drupal\singpost_protection\Utils\Protection;

/**
 * Class LocateUsForm
 *
 * @package Drupal\singpost_toolbox_locate_us\Form\Frontend
 */
class LocateUsForm extends FrontendFormBase{

	/**
	 * @return string
	 */
	public function getFormId(){
		return 'frontend_locate_us_form';
	}

	/**
	 * @param array $form
	 * @param string $position
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 *
	 * @return array
	 */
	public function buildForm(
		array $form,
		FormStateInterface $form_state,
		$position = 'node'){

		$model = new LocateUsType();

		if ($position == 'node'){
			$user_submission = $this->getParams();
		}else{
			$user_submission = [];
		}

		$form['#attributes'] = [
			'class' => ['main-form toolbox-form frontend-locate-us ' . ($position == 'node' ? 'node-form' : 'side-form')],
		];

		$form['#attached'] = [
			'library' => [
				'singpost_toolbox_locate_us/google_map',
				'singpost_toolbox_locate_us/map-locate-us'
			]
		];

		$form['row'] = [
			'#type'       => 'container',
			'#attributes' => [
				'class' => [
					($position == 'node') ? '' : 'track-trace-sec__tab-cont'
				]
			]
		];

		if ($position == 'node'){
			$resetval = \Drupal::request()->query->get('reset');
			if($resetval == 1 && $position == 'node'){
				$user_submission = [];
				$session = $this->getRequest()->getSession();
				$session->remove($this->getFormId());
				$session->remove('data_' . $this->getFormId());
			}
			$types = $model->getTypes();
			if (!empty($types)){
				foreach ($types as $type){
					$form['locate_us_type'][$type->id] = [
						'#type'          => 'radio',
						'#parents'       => ['locate-us-type'],
						'#return_value'  => $type->id,
						'#default_value' => $user_submission['locate-us-type'] ?? NULL,
						'#attributes'    => [
							'id'    => 'locate-us-type-' . $type->id,
							'class' => ['hidden hide']
						],
					];
				}
			}
		}else{
			$form['row']['locate-us-type'] = [
				'#type'           => 'select',
				'#options'        => $model->getListType(),
				'#title'          => $this->t('Find'),
				'#title_display'  => 'invisible',
				'#required'       => TRUE,
				'#required_error' => t('Please select which location type you are searching for.'),
				'#attributes' => [
					'class' => [
						($position == 'node') ? '' : 'track-trace-sec__calculate-drop'
					]
				]
			];
		}

		$form['row']['keyword'] = [
			'#type'          => 'textfield',
			//'#title'         => $this->t('Near'),
			//'#title_display' => ($position == 'node') ? 'before' : 'invisible',
			'#default_value' => $user_submission['keyword'],
			'#prefix'        => t('<img src="/themes/singpostd9/assets/images/location-point.svg" alt="icon" class="sgp-text-box__icon" id="cur-loc-icon">'),
			'#attributes'    => [
				'placeholder'  => $this->t('Enter a location'),
				'class'        => ['sgp-text-box__input map-autocomplete form-control-lg'],
				'autocomplete' => 'off',
				'target-id'    => 'place-id-' . $position
			],
		];

		if($position == 'side'){
			$form['side-sgp-link'] = [
				'#type'   => 'item',
				'#markup' => t("<div class='track-trace-sec__track-tab-footer'>Take a look at our locations across Singapore. Click
				<a href='/locate-us' class='track-trace-sec__link'>Here</a> to find out more.</div>")
			];
		}

		$form['row']['place_id'] = [
			'#type'          => 'hidden',
			'#default_value' => $user_submission['place_id'] ?? '',
			'#attributes'    => ['id' => 'place-id-' . $position]
		];

		$form['row']['actions'] = [
			'#type'       => 'actions',
			'#attributes' => [
				'class' => ['text-right'],
			],
		];

		$form['row']['actions']['submit'] = [
			'#type'       => 'submit',
			'#value'      => t('Find'),
			'#attributes' => [
				'class' => [
					'btn btn-form-submit sgp-link-btn sgp-link-btn--box span-wrapper',
				],
			],
		];

		if ($this->config_recaptcha->get('site_key')){
			$form['recaptcha']                      = [
				'#markup' => '<div class="modal fade" tabindex="-1" role="dialog" id="recaptcha-modal-locate-us"><div class="modal-dialog modal-dialog-centered"><div class="modal-content recaptcha-modal"><div class="checkbox"><div id="recaptcha-locate-us"></div></div></div></div></div>'
			];
			$form['g_recaptcha_response_locate_us'] = [
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
	 *
	 * @return void
	 */
	public function submitForm(array &$form, FormStateInterface $form_state){
		if (class_exists('Protection')){
			try{
				new Protection('locate_us');
			}catch (Exception $ex){
			}
		}

		$this->getRequest()->getSession()->remove('locate_us_get_method');

		parent::submitForm($form, $form_state);

		$form_state->setRedirect('singpost.toolbox.locate_us.index');
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 */
	public function validateForm(array &$form, FormStateInterface $form_state){
		if (class_exists('Protection')){
			try{
				$protection = new Protection('locate_us', ['READ_ONLY' => TRUE]);

				if ($protection->status == $protection::CAPTCHA){
					$recaptcha_token = $form_state->getValue('g_recaptcha_response_locate_us');

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

		if (empty($form_state->getValue('locate-us-type'))){
			$form_state->setErrorByName('locate-us-type',
				'Please select which location type you are searching for.');
		}

		if ($form_state::hasAnyErrors()){
			$this->clearForm();
		}
	}

	/**
	 * @return array|int|mixed|\SimpleXMLElement|string
	 */
	public function getResults(){
		$helper = new LocateUs();
		$data   = [];

		$values = $this->getParams();
		
		if (!empty($values['locate-us-type'])){
			$data['type']     = $values['locate-us-type'];
			$data['keyword']  = $values['keyword'] ?? NULL;
			$data['place_id'] = $values['place_id'] ?? NULL;
			$data['url']      = $values['url'];
			
			$model = LocateUsType::findOne($data['type']);
			
			if (!empty($model)){
				$type              = LocateUs::formatType($data['type']);
				$data['icon']      = $model->getImage($model->icon);
				$data['icon_text'] = $model->icon_text;
				$data['marker']    = $model->getImage($model->marker);
				
			}

			if (!empty($type)){
				$data['results'] = $helper->getLocate($type, $data['keyword']);
			}
		}
		
		return $data;
	}

	/**
	 * @return bool
	 */
	protected function _valueGetFromUrl(){
		$request = $this->getRequest();
		$session = $request->getSession();

		//key search is required, if outlettype null, then type = 1
		if ($request->get('search', '')){
			$search = $request->get('search', '');

			$outlet_type = 1;

			if ($request->get('outlettype', '')){
				$outlet_type = $request->get('outlettype', '');
			}

			$session->set('locate_us_get_method', ['search' => $search, 'type' => $outlet_type]);
		}

		return TRUE;
	}

	/**
	 * @return array
	 */
	public function getParams(){
		$values = [
			'keyword'        => NULL,
			'locate-us-type' => NULL,
			'place_id'       => NULL,
			'url'            => FALSE
		];

		$this->_valueGetFromUrl();
		$submission = $this->_getSubmission($this->getFormId());

		$session = $this->getRequest()->getSession();

		$value_from_url = $session->get('locate_us_get_method');
		
		if ($value_from_url){
			
			$values['keyword'] = $value_from_url['search'];
			//type is number, ex: 1, 2
			$values['locate-us-type'] = $value_from_url['type'];
			$values['url']            = TRUE;
		}

		if (empty($values['keyword']) && $submission){
			
			//if form is submission, then remove session from GET method
			$session->remove('locate_us_get_method');
			//type is number get from form, ex: 1, 2, so convert to string
			$values['locate-us-type'] = $submission['locate-us-type'];
			$values['keyword']        = $submission['keyword'];

			$values['place_id'] = $submission['place_id'];

		}else{
			//if get value from GET method, then remove submission form
			$this->clearForm();
		}
		return $values;
	}
}
