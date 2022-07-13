<?php


namespace Drupal\singpost_toolbox_track_and_trace\Frontend\Form;


use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\singpost_toolbox\Form\Frontend\FrontendFormBase;
use Drupal\singpost_toolbox\Helper\Recaptcha;
use Drupal\singpost_toolbox_track_and_trace\Helper\TrackAndTrace;
use Exception;
use Drupal\singpost_protection\Utils\Protection;

/**
 * Class TrackAndTraceForm
 *
 * @package Drupal\singpost_toolbox_track_and_trace\Form\Frontend
 */
class TrackAndTraceForm extends FrontendFormBase{

	/**
	 * @return string
	 */
	public function getFormId(){
		return 'track_and_trace_frontend_form';
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 *
	 * @param string $position
	 *
	 * @return array
	 */
	public function buildForm(array $form, FormStateInterface $form_state, $position = 'node'){
		$helper  = new TrackAndTrace();
		$user_submissions = ($position == 'node') ? $this->_getSubmission($this->getFormId()) : [];

		$form['#attributes'] = [
			'class' => ['main-form toolbox-form frontend-tnt' . ($position == 'node' ? 'node-form' : 'side-form')]
		];

		$form['#attached'] = [
			'library' => [
				'singpost_toolbox/toolbox',
				'singpost_toolbox_track_and_trace/form'
			]
		];

		$form['#action'] = Url::fromRoute('singpost.toolbox.track_and_trace.index')->toString();

		$form['row'] = [
			'#type'       => 'container',
			'#attributes' => [
				'class' => [
					'row2'
				]
			]
		];

		$form['row']['field_wrapper'] = [
			'#type'       => 'container',
			'#attributes' => [
				'class' => [
					($position == 'node') ? 'sgp-search' : 'track-trace-sec__tab-cont'
				]
			]
		];

	/*	$form['row']['field_wrapper']['left'] = [
			'#type'       => 'container',
			'#attributes' => [
				'class' => [
					($position == 'node') ? 'col-lg-6 col-12' : 'col-12'
				]
			]
		];

		

		$form['row']['field_wrapper']['right'] = [
			'#type'       => 'container',
			'#attributes' => [
				'class' => [
					($position == 'node') ? 'col-lg-6 col-12' : 'col-12'
				]
			]
		]; */

		$form['old_value'] = [
			'#type'  => 'hidden',
			'#value' => $user_submissions['old_value'] ?? ''
		];

		$markup = '';

		if (!empty($user_submissions['old_value'])){
			$recent = array_unique($this->_filterTrackingNumbers($user_submissions['old_value']));

			if (!empty($recent)){
				foreach ($recent as $item){
					$markup .= "<div class='rec-item text-color-primary mb-2'>" . $item . "</div>";
				}
			}
		}

		$form['row']['field_wrapper']['tracking_numbers'] = [
			'#title'         => t('Tracking Number'),
			'#title_display' => ($position == 'node') ? 'invisible' : 'invisible',
			'#type'          => 'textfield',
			'#required'      => TRUE,
			'#placeholder'   => t('Enter tracking numbers separated by comma. (max 20 numbers)'),
			'#default_value' => $user_submissions['tracking_numbers'] ?? '',
			'#maxlength' => 1024,
			'#attributes'    => [
				'id' => ($position == 'node') ? 'tracking-numbers-node' : 'tracking-numbers-side',
				'class' => [
					($position == 'node') ? 'sgp-search__input' : 'track-trace-sec__input-text'
				]
			]
			
		];

		/*$form['row']['right']['recent_queries'] = [
			'#type'   => 'item',
			'#markup' => t("<div class='font-weight-bold font-16 mb-2'>Recent queries</div><div class='recent-queries'>" . $markup . "</div>")
		];*/

		$form['row']['actions'] = [
			'#type'       => 'actions',
			'#attributes' => [
				'class' => [
					($position == 'node') ? 'col-lg-6' : 'col-12'
				]
			]
		];

		$form['row']['field_wrapper']['actions']['submit'] = [
			'#type'       => 'submit',
			'#value'      => t('SEARCH NOW'),
			'#attributes' => [
				'class' => [
					'btn btn-form-submit','sgp-link-btn sgp-link-btn--box','sgp-search','span-wrapper',
				]
			]
			
		];
		if($position == 'node'){
			$form['row']['']['sgp-link'] = [
				'#type'   => 'item',
				'#markup' => t("<a href='/item-enquiry' title='Need help?' class='sgp-link'>Need help?</a>")
			];
	
			$other_note_value = $helper->getOtherNotes();
	
			$form['row']['']['other_note'] = [
				'#type'   => 'item',
				'#markup' => $other_note_value
			];
		}
		if($position == 'side'){
			$form['row']['side-sgp-link'] = [
				'#type'   => 'item',
				'#markup' => t("<div class='track-trace-sec__track-tab-footer'>For further assistance, you may click <a href='/track-items' class='track-trace-sec__link'>here</a> to find out more.</div>")
			];
		}
		
		if ($this->config_recaptcha->get('site_key')){
			$form['recaptcha']                            = [
				'#markup' => '<div class="modal fade" tabindex="-1" role="dialog" id="recaptcha-modal-track-and-trace"><div class="modal-dialog modal-dialog-centered"><div class="modal-content recaptcha-modal"><div class="checkbox"><div id="recaptcha-track-and-trace"></div></div></div></div></div>'
			];
			$form['g-recaptcha-response-track-and-trace'] = [
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
	 */
	public function validateForm(array &$form, FormStateInterface $form_state){
		$helper  = new TrackAndTrace();
		$tracking_numbers = $form_state->getValue('tracking_numbers');
		if (class_exists('Protection')){
			try{
				$protection = new Protection('track_and_trace', ['READ_ONLY' => TRUE]);
				if ($protection->status == $protection::EXCEEDLIMIT){
					$form_state->setErrorByName('error',
						t('Error: You have exceeded the maximum number of times allowed.<br/>Please try again later.'));
				}

				if ($protection->status == $protection::BLACKLIST){
					$form_state->setErrorByName('error',
						t('You are not allow to track the item, please contact our customer service for more support."'));
				}
				if ($protection->status == $protection::CAPTCHA){
					$recaptcha_token = $form_state->getValue('g-recaptcha-response-track-and-trace');

					$site_key   = $this->config_recaptcha->get('site_key');
					$secret_key = $this->config_recaptcha->get('secret_key');

					if ($site_key && $secret_key){
						$recaptcha          = new Recaptcha($site_key, $secret_key);
						$recaptcha_response = $recaptcha->verifyResponse($recaptcha_token);

						if (isset($recaptcha_response['success'])){
							if ($recaptcha_response['success']){
								$protection->setCaptchaDuration();
							}else{
								$error_msg = is_array($recaptcha_response['error-codes']) ? $recaptcha_response['error-codes'][0] : $recaptcha_response['error-codes'];

								$form_state->setErrorByName('error',
									t('Error: ' . $error_msg));
							}
						}
					}
				}

			}catch (Exception $exception){
			}
		}
		if (!$tracking_numbers){
			$form_state->setErrorByName('tracking_numbers',
				t('Tracking number field is required.'));
		}else{
			$tracking_nos = $this->_filterTrackingNumbers($tracking_numbers);

			if (count($tracking_nos) > 20){
				$form_state->setErrorByName('tracking_numbers',
					t('Sorry, you have exceed the maximum limit of 20 track number per request, please try again.'));
			}

			// if (!empty($tracking_nos)){
			// 	$getErrorMessage = $helper->getErrorMessage();
			// 	foreach ($tracking_nos as $no){

			// 		if (!((strlen($no) >= 9 && strlen($no) <= 15) || strlen($no) == 21)){
			// 			$form_state->setErrorByName('tracking_numbers',t($getErrorMessage));
			// 			//break;
			// 		}
			// 	}
			// }
		}

		if ($form_state::hasAnyErrors()){
			$this->clearForm();
		}
	}

	public function trackingNumberValidate($trackingNumber){
		$helper  = new TrackAndTrace();
		if (!empty($trackingNumber)){
			foreach ($trackingNumber as $no){
				if (!((strlen($no) >= 9 && strlen($no) <= 15) || strlen($no) == 21)){
					return true;
				}
			}
		}
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 *
	 * @return void
	 */
	public function submitForm(array &$form, FormStateInterface $form_state){
		$submission = $this->_getSubmission($this->getFormId());
		$old_value  = $submission['tracking_numbers'] ?? '';
		$form_state->setValue('old_value', $old_value);
		if (class_exists('Protection')){
			try{
				new Protection('track_and_trace');
			}catch (Exception $ex){
			}
		}
		parent::submitForm($form, $form_state);
		$form_state->setRedirect('singpost.toolbox.track_and_trace.index');
	}

	/**
	 * @return array|int|mixed|\SimpleXMLElement|string
	 */
	public function getResults(){
		$track_nos = $this->getTrackingNumbers();
		$helper    = new TrackAndTrace();
		// $trackFormat = $this->trackingNumberValidate($track_nos);
		// if($trackFormat){
		// 	$getErrorMessage = 'Wrong';
		// }
		if (!empty($track_nos)){
			return [
				'data'         => $helper->getApiResults($track_nos),
				'tracking_nos' => $track_nos,
				//'track_formt'  => $getErrorMessage
			];
		}

		return - 1;
	}

	/**
	 * @return array|mixed
	 */
	public function getTrackingNumbers(){
		$submission = $this->_getSubmission($this->getFormId());

		if ($submission){
			$track_ids = $submission['tracking_numbers'];

			if ($track_ids){
				$tracking_nos = $this->_filterTrackingNumbers($track_ids);

				return array_unique($tracking_nos);
			}
		}

		return [];
	}

	/**
	 * @param $tracking_ids
	 *
	 * @return array|mixed
	 */
	private function _filterTrackingNumbers($tracking_ids){
		$tracking_nos = str_replace(" ", ",", $tracking_ids);

		return array_filter(array_map('trim', explode(",", $tracking_nos)));
	}
}
