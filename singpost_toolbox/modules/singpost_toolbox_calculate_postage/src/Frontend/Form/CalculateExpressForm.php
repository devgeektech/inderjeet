<?php


namespace Drupal\singpost_toolbox_calculate_postage\Frontend\Form;


use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\singpost_toolbox_calculate_postage\Helper\CalculateHelper;

/**
 * Class loremipsumForm.
 *
 * @package Drupal\loremipsum\Form
 */
class CalculateExpressForm extends FormBase{

	/**
	 * {@inheritdoc}
	 */
	public function getFormId(){
		return 'ajax_calculate_express_form';
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildForm(array $form, FormStateInterface $form_state){
		$form['#attached']        = [
			'library' => [
				'singpost_toolbox_calculate_postage/form'
			]
		];
		$form['row_1'] = [
			'#type'       => 'container',
			'#attributes' => [
				'class' => ['sgp-text-box']
			]
		];
		$form['row_2'] = [
			'#type'       => 'container',
			'#attributes' => [
				'class' => ['sgp-text-box']
			]
		];
		$form['row_1']['from_postal_code_title'] = [
			'#type'   => 'markup',
			'#markup' => t("<p class='sgp-text-box__text'>Postal code from:</p>"),
		];
		$form['row_1']['from_postal_code'] = [
			'#type'       => 'textfield',
			'#title'      => $this->t('Postal code from:'),
			'#title_display' => 'invisible',
			'#maxlength'  => 6,
			// '#ajax'       => [
			// 	'callback' => [$this, 'getData'],
			// 	//'event'    => 'end_typing',
			// 	// 'progress' => [
			// 	// 	'type'    => 'throbber',
			// 	// 	'message' => $this->t('Searching'),
			// 	// ],
			// ],
			'#attributes' => [
				'class'      => [
					'delayed-input-submit border sgp-text-box__input'
				],
				'data-delay' => '2000',
			],
			'#suffix'     => '<small id="error-from" class="text-danger"></small>',
		];
		$form['row_2']['to_postal_code_title'] = [
			'#type'   => 'markup',
			'#markup' => t("<p class='sgp-text-box__text'>Postal code to:</p>"),
		];
		$form['row_2']['to_postal_code']   = [
			'#type'       => 'textfield',
			'#title'      => $this->t('Postal code to:'),
			'#title_display' => 'invisible',
			'#maxlength'  => 6,
			// '#ajax'       => [
			// 	'callback' => [$this, 'getData'],
			// 	'event'    => 'end_typing',
			// 	'progress' => [
			// 		'type'    => 'throbber',
			// 		'message' => $this->t('Searching'),
			// 	],
			// ],
			'#attributes' => [
				'class'      => [
					'delayed-input-submit border sgp-text-box__input'
				],
				'data-delay' => '2000',
			],
			'#suffix'     => '<small id="error-to" class="text-danger"></small>',
		];
		
		$form['total']   = [
			'#type'   => 'label',
			'#suffix' => "<div id='price_express'><strong>Total: </strong> <span id='total-result'></span></div>",
		];
		$form['actions'] = [
			'#type'       => 'actions',
		];
		$form['actions']['submit'] = [
			'#type' => 'button',
			'#value' => t('CALCULATE'),
			'#ajax' => [
			  'callback' => [$this, 'getData'],
			  'method' => 'replace',
			  'event'    => 'click',
			  'progress' => [
				'type'    => 'throbber',
				'message' => $this->t('Calculating'),
				],
			],
			'#attributes' => [
				'class'      => [
					'sgp-link-btn sgp-link-btn--box'
				]
			]
		];
		$form['weight']           = [
			'#type'          => 'hidden',
			'#default_value' => '',
			'#attributes'    => [
				'id' => 'express-weight',
			]
		];
		$form['dimension']        = [
			'#type'          => 'hidden',
			'#default_value' => '',
			'#attributes'    => [
				'id' => 'express-dimension',
			]
		];


		return $form;
	}

	/**
	 * {@inheritdoc}
	 */
	public function validateForm(array &$form, FormStateInterface $form_state){

	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 *
	 */
	public function submitForm(array &$form, FormStateInterface $form_state){

	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 *
	 * @return array|\Drupal\Core\Ajax\AjaxResponse|mixed|\SimpleXMLElement|string
	 */
	public function getData(array $form, FormStateInterface $form_state){
		$ajax_response = new AjaxResponse();

		$helper           = new CalculateHelper();
		$data             = [];
		$dimension        = [];
		$from_postal_code = $form_state->getValue('from_postal_code');
		$to_postal_code   = $form_state->getValue('to_postal_code');
		$weight           = $form_state->getValue('weight');

		if (!empty($from_postal_code) && strlen($from_postal_code) < 6){
				$ajax_response->addCommand(new HtmlCommand('#error-from',
				'Please enter valid from postal code.'));
				$data = '';
				$ajax_response->addCommand(new HtmlCommand('#total-result', $data));
			return $ajax_response;
		}else{
			$ajax_response->addCommand(new HtmlCommand('#error-from', ''));
		}
		if (!empty($to_postal_code) && strlen($to_postal_code) < 6){
			$ajax_response->addCommand(new HtmlCommand('#error-to',
				'Please enter valid to postal code.'));
				$data = '';
				$ajax_response->addCommand(new HtmlCommand('#total-result', $data));
			return $ajax_response;
		}else{
			$ajax_response->addCommand(new HtmlCommand('#error-to', ''));
		}


		$size = $helper->getSize($form_state->getValue('dimension'));

		if (!empty($size)){
			$dimension = [
				'size'   => $size->size_code,
				'length' => $size->length,
				'width'  => $size->width,
				'height' => $size->height
			];
		}


		if (!empty($from_postal_code) && !empty($to_postal_code) && !empty($weight) && !empty($dimension)){
			$data = $helper->calculateForSingapore($weight, 'g',
				$dimension, $from_postal_code, $to_postal_code);

			$data = $helper->getMaximumPrice($data);
		}


		$ajax_response->addCommand(new HtmlCommand('#total-result', $data));

		return $ajax_response;
	}
}