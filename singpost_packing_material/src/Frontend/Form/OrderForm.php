<?php


namespace Drupal\singpost_packing_material\Frontend\Form;


use Drupal;
use Exception;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Url;


#use Drupal\singpost_toolbox\Form\Frontend\FrontendFormBase;
use Drupal\singpost_toolbox\Helper\Recaptcha;
use Drupal\singpost_toolbox_calculate_postage\Helper\CalculateHelper;
use Drupal\singpost_protection\Utils\Protection;


use Drupal\singpost_base\ModelInterface;
use Drupal\singpost_packing_material\Form\Config\PackingMaterialConfigForm;
use Drupal\singpost_packing_material\Model\PackingMaterialProduct;
use Symfony\Component\HttpFoundation\Request;


/**
 * Class OrderForm
 *
 * @package Drupal\singpost_packing_material\Frontend\Form
 */
class OrderForm extends FormBase{

	use MessengerTrait;

	/**
	 * @var \Drupal\singpost_base\ModelInterface
	 */
	protected $_order;

	/**
	 * @var \Drupal\singpost_base\ModelInterface
	 */
	protected $_order_detail;

	protected $_request;

	/**
	 * OrderForm constructor.
	 *
	 * @param \Drupal\singpost_base\ModelInterface $order
	 * @param \Drupal\singpost_base\ModelInterface $order_detail
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 */

	public $config_recaptcha;
 	public function __construct(
		ModelInterface $order,
		ModelInterface $order_detail,
		Request $request){
		$this->_order        = $order;
		$this->_order_detail = $order_detail;
		$this->_request      = $request;
		$this->config_recaptcha = $this->config('simple_recaptcha.config');
	}

	/**
	 * @return string
	 */
	public function getFormId(){
		return 'pm_frontend_order_form';
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 *
	 * @return array
	 */
	public function buildForm(array $form, FormStateInterface $form_state){
		$form['#attached']['library'][] = 'singpost_packing_material/packing-material-form';

		$form['#attributes'] = ['class' => ['main-form pm-frontend-order-form']];

	/*	$form['row'] = [
			'#type'       => 'container',
			'#attributes' => ['class' => ['row']]
		];

		$form['row']['col_left'] = [
			'#type'       => 'container',
			'#weight'     => 1,
			'#attributes' => ['class' => ['col-md-6']]
		];

		$form['row']['col_right'] = [
			'#type'       => 'container',
			'#weight'     => 2,
			'#attributes' => ['class' => ['col-md-6']]
		];*/
		$form['salutation'] = [
			'#type' => 'radios',
			'#title' => t('Salutation'),
			/*'#description' => t('Select a method for deleting annotations.'),*/
			'#options' => array('Mr.' => 'Mr.', 'Ms.' => 'Ms.', 'Mdm' => 'Mdm'),
			'#default_value' => 'Mr.',
			 
			  '#attached' => array(
		        'library' => array(
		          'core/jquery',
		          'rate_field/admin-star-field',
		        ),
		      ),
		      '#attributes' => array(
		        'class' => array(
		          'stars',
		        ),
		      ),
			];

		$form['name'] = [
			'#type'     => 'textfield',
			/*'#title'    => t('Full Name'),*/
			'#required' => TRUE,
			'#attributes' => ['class' => ['sgp-text-box__input']]
		];

		$form['company'] = [
			'#type'  => 'textfield',
			/*'#title' => t('Company Name'),*/
			'#attributes' => ['class' => ['sgp-text-box__input']]
		];

		$form['email'] = [
			'#type'     => 'textfield',
			/*'#title'    => t('Email'),*/
			'#required' => TRUE,
			'#attributes' => ['class' => ['sgp-text-box__input']]
		];

		$form['contact_number'] = [
			'#type'     => 'textfield',
			/*'#title'    => t('Contact Number'),*/
			'#required' => TRUE,
			'#attributes' => ['class' => ['sgp-text-box__input']]
		];

		$form['account_number'] = [
			'#type'     => 'textfield',
			/*'#title'    => t('SingPost Corporate Account Number'),*/
			'#required' => TRUE,
			'#attributes' => ['class' => ['sgp-text-box__input']]
		];

		$form['block_number'] = [
			'#type'     => 'textfield',
			/*'#title'    => t('Block/House number'),*/
			'#required' => TRUE,
			'#attributes' => ['class' => ['sgp-text-box__input']]
		];

		$form['street_address'] = [
			'#type'     => 'textfield',
			/*'#title'    => t('Street Address'),*/
			'#required' => TRUE,
			'#attributes' => ['class' => ['sgp-text-box__input']]
		];

		$form['unit_number'] = [
			'#type'  => 'textfield',
			/*'#title' => t('Unit Number'),*/
			'#attributes' => ['class' => ['sgp-text-box__input']]
		];

		$form['postal_code'] = [
			'#type'     => 'textfield',
			/*'#title'    => t('Postal Code'),*/
			'#required' => TRUE,
			'#attributes' => ['class' => ['sgp-text-box__input']]
		];

		$form['product_dt'] = array(
		    '#type' => 'hidden',
		    '#value' =>'', 
		    '#attributes' => ['id' => ['edit-product_dt']]
		);

	/*	$form['row']['col_9'] = [
			'#type'       => 'container',
			'#weight'     => 3,
			
		];*/

		$form['description'] = [
			

			'#markup' => t("<ul class='sgp-checkout__notes '><li> You will receive a confirmation email after submitting an online order.</li>
				<li> We will be contacting you within 3 working days to confirm your order and payment method. </li>
				<li class='mb-3'> No online payment is required when submitting your orders.</li>",)
		];

/*		$form['row']['col_3'] = [
			'#type'       => 'container',
			'#weight'     => 4,
			'#attributes' => ['class' => ['col-md-3 text-md-left text-center mt-md-0 mt-3']]
		];

		$form['row']['col_3']['actions'] = [
			'#type'       => 'actions',
			'#attributes' => ['class' => ['m-0']]
		];*/

		/*$form['tnc'] = array(
		  '#type' => 'checkbox',
		  '#required' => TRUE,
		  '#title' => t('By submitting, you acknowledge and accept the Privacy Policy and Website Terms of Use of SingPost Group.'),
		  '#default_value' => TRUE,
		);*/
		$form['tnc'] = array(
		  '#type' => 'checkbox',
		  '#title' => t('By submitting, you acknowledge and accept the Privacy Policy and Website Terms of Use of SingPost Group'),
		  #'#title_display' => 'invisible',
		  '#field_suffix' => '',
 		  '#default_value' => 0,
		  '#required' => TRUE,
		  '#required_error' => t('This field is required.'),
		  '#attributes' => ['data-msg-required' => ['This field is required']],

		);

		$form['submit'] = [
			'#type'       => 'submit',
			'#value'      => t('Submit'),
			'#attributes' => ['class' => ['btn btn-form-submit']],
		];

		 				
        if ($this->config_recaptcha->get('site_key')){
	      $form['recaptcha']                     = [
	        '#markup' => '<div class="checkbox"><div id="recaptcha-coversea"></div></div>'
	      ];
	      $form['g-recaptcha-response'] = [
	        '#type'           => 'textarea',
	        '#attributes'     => [
	          'class' => ['d-none']
	        ],
	        '#theme_wrappers' => [],
	      ];
	    }


		$form['detail'] = [
			'#type'       => 'container',
			'#weight'     => 5,
			'#attributes' => ['class' => ['form-item-detail']]
		]; 

		return $form;
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 */
	public function submitForm(array &$form, FormStateInterface $form_state){

	  try{
        new Protection('frontend_order_form');
      }catch (Exception $exception){
      	$form_state->setErrorByName('error',
                t('Error: ' . 'Protection function '));
      }
  

		$form_state->cleanValues();

		$values = $form_state->getValues();

		$product_dt = $form_state->getUserInput()['product_dt'];
		if(!empty($product_dt)){
			$detail = json_decode($product_dt,true);
		}else{
			$detail = array();
		}
 
	 if (is_array($detail) && !empty($detail)) {
	 	 
		if ($detail['grand_total'] && floatval($detail['grand_total']) >= 150){
			$products = [];

			$confirm = Url::fromRoute('singpost.pm.confirm');

			$info = [
				'name'              => $values['name'],
				'company_name'      => $values['company'],
				'email'             => $values['email'],
				'contact_number'    => $values['contact_number'],
				'block_number'      => $values['block_number'],
				'street_name'       => $values['street_address'],
				'unit_number'       => $values['unit_number'],
				'postal_code'       => $values['postal_code'],
				'sp_account_number' => $values['account_number'],
				'subtotal'          => round($detail['sub_total'], 2),
				'total'             => round($detail['grand_total'], 2),
				'discount'          => round($detail['discount'], 2),
				'order_date'        => Drupal::time()->getCurrentTime()
			];

			$this->_order->load($info);

			if ($this->_order->save()){

				if (!empty($detail)){
					foreach ($detail['cartItems'] as $key => $item){
						if (is_array($item)){
							$products[] = [
								'order_id'   => $this->_order->id,
								'product_id' => $item['id'],
								'name'		 => $item['name'],
								'price'      => $item['price'],
								'quantity'   => $item['qty']
							];
						}
					}



					$this->_order_detail->load($products);

					if ($this->_order_detail->createMultiple($products)){

						$data           = $this->_order;
						$email_customer = $data->email;
						$order_detail   = $data->getOrderDetail($data->id);

						$this->_sendNotifyStaff($data, $order_detail,
							$info['subtotal'], $info['discount'],
							$info['total']);

						$this->_sendNotifyCustomer($data, $order_detail,
							$info['subtotal'], $info['discount'],
							$info['total'], $email_customer);

						//$form_state->setRedirectUrl($confirm);
						$url = \Drupal\Core\Url::fromRoute('singpost.pm.thanks')
			          			->setRouteParameters(array('id'=>$this->_order->id));
						$form_state->setRedirectUrl($url);
					}else{
						$this->messenger()
						     ->addError(t('Something went wrong. Cannot save order detail.'));
						$form_state->setRebuild();
					}
				}
			}else{
				$this->messenger()->addError(t('Something went wrong. Cannot save order.'));
				$form_state->setRebuild();
			}
		}else{
			$this->messenger()
			     ->addError(t('A minimum order of S$150 is required for online orders.'));
			$form_state->setRebuild();
		}
		}else{
			$this->messenger()
			     ->addError(t('Your cart is empty!'));
			$form_state->setRebuild();
		}
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 */
	public function validateForm(array &$form, FormStateInterface $form_state){

	 

		if (!$form_state->getValue('name')){
			$form_state->setErrorByName('name', t('Please input Name'));
		}

		$email = $form_state->getValue('email');

		if (!$email){
			$form_state->setErrorByName('email', t('Please input Email'));
		}else{
			if (!Drupal::service('email.validator')->isValid($email)){
				$form_state->setErrorByName('email', t('Please input a valid email address'));
			}
		}

		$block_number = $form_state->getValue('block_number');

		if (!$block_number){
			$form_state->setErrorByName('block_number', t('Please input Block/House Number'));
		}/*else{
			if (!is_numeric($block_number) || (is_numeric($block_number) && $block_number < 0)){
				$form_state->setErrorByName('block_number',
					t('Please enter a valid block house number.'));
			}
		}*/

		if (!$form_state->getValue('street_address')){
			$form_state->setErrorByName('street_address', t('Please input Street Address'));
		}

		if (!$form_state->getValue('postal_code')){
			$form_state->setErrorByName('postal_code', t('Please input Postal Code'));
		}

		if (!$form_state->getValue('account_number')){
			$form_state->setErrorByName('account_number',
				t("SingPost Corporate Account Number is required. If you do not have one, please proceed to click <a href='https://shop.singpost.com/packaging-materials.html' target='_blank'> here</a> to make payment online"));
		}

		$pattern = '/^(?=(?!66666666|88888888|99999999|87654321))+((6|8|9)+(\d{7}))$/';

		$contact_number = $form_state->getValue('contact_number');

		if (!$contact_number){
			$form_state->setErrorByName('contact_number', t('Please input Contact Number'));
		}else{
			if (!preg_match($pattern, $contact_number)){
				$form_state->setErrorByName('contact_number',
					t('Please input a valid Contact Number'));
			}
		}



		try{
        $protection = new Protection('frontend_order_form', ['READ_ONLY' => TRUE]);

        //print_r($form_state);
        if ($protection->status == TRUE){
          $recaptcha_token = $form_state->getValue('g-recaptcha-response');
          //var_dump($recaptcha_token);
          $site_key        = $this->config_recaptcha->get('site_key');
          $secret_key      = $this->config_recaptcha->get('secret_key');

          if ($site_key && $secret_key){
            $recaptcha          = new Recaptcha($site_key, $secret_key);
            $recaptcha_response = $recaptcha->verifyResponse($recaptcha_token);

            if (isset($recaptcha_response['success']) && !$recaptcha_response['success']){
              $error_msg = is_array($recaptcha_response['error-codes']) ? $recaptcha_response['error-codes'][0] : $recaptcha_response['error-codes'];

              $form_state->setErrorByName('error',
                t('Error: ' . 'Invalid captcha '));
            }
          }
        }else{
        	 $form_state->setErrorByName('error',
            t('Protection function not configured else'));
        }

        if ($protection->status == $protection::BLACKLIST){
          $form_state->setErrorByName('error',
            t('You are not allow to track the item, please contact our customer service for more support.'));
        }

      }catch (Exception $exception){
      	 $form_state->setErrorByName('error',
            t('Protection function not configured'));
      }

	}

	/**
	 * @param $mail
	 * @param $to
	 * @param $info
	 * @param $detail
	 * @param $subtotal
	 * @param $discount
	 * @param $total
	 */
	protected function _sendNotify(
		$mail,
		$to,
		$info,
		$detail,
		$subtotal,
		$discount,
		$total){
		$mail_manager  = Drupal::service('plugin.manager.mail');
		$token_manager = Drupal::token();
		$module        = 'singpost_packing_material';
		$key           = 'packing_material_notify';

		$raw = $token_manager->replace($mail['message']);


		if (!empty($detail)){
			foreach ($detail as $value){
				if (is_string($value->product_id)){
					$value->product_id = PackingMaterialProduct::findOne($value->product_id);
				}
			}
		}

		$body = [
			'#theme' => 'singpost_email_notify_order_detail',
			'#data'  => [
				'items'    => $detail,
				'subtotal' => $subtotal,
				'discount' => $discount,
				'total'    => $total,
			]
		];

		$body = Drupal::service('renderer')->render($body);

		$delivery_address = ($info->unit_number ? $info->unit_number . ', ' : '') . ($info->block_number ? $info->block_number . ', ' : '') . ($info->street_name ? $info->street_name . ', ' : '') . $info->postal_code;

		$date = ($info->order_date ? Drupal::service('date.formatter')
		                                   ->format($info->order_date, 'custom', 'Y-m-d h:m') : '');

		$search  = ['[order:id]', '[order:detail]', '[order:date]', '[customer:name]', '[customer:company]', '[customer:email]', '[customer:contact_number]', '[customer:account_number]', '[customer:delivery_address]'];
		$replace = [$info->id, $body, $date, $info->name, $info->company_name, $info->email, $info->contact_number, $info->sp_account_number, $delivery_address];

		$theme = str_replace($search, $replace, $raw);

		$langcode = 'en';

		$params['from']    = $mail['from'];
		$params['subject'] = str_replace('[order:id]', $info->id, $mail['subject']);
		$params['body']    = $theme;

		$result = $mail_manager->mail($module, $key, $to, $langcode, $params, NULL, TRUE);

		if ($result['result'] !== TRUE){
			$this->messenger()
			     ->addError('There was a problem sending your mail and it was not sent.');

		}
	}

	/**
	 * @param $info
	 * @param $detail
	 * @param $subtotal
	 * @param $discount
	 * @param $total
	 * @param $mail_customer
	 *
	 * @return bool|void
	 */
	private function _sendNotifyCustomer(
		$info,
		$detail,
		$subtotal,
		$discount,
		$total,
		$mail_customer){
		$config = Drupal::config(PackingMaterialConfigForm::$config_name);

		$params            = [];
		$params['subject'] = $config->get('customer_subject');
		$params['message'] = $config->get('customer_message')['value'];
		$params['from']    = $config->get('customer_from_email');

		if (!empty($mail_customer)){
			return $this->_sendNotify($params, $mail_customer, $info, $detail,
				$subtotal, $discount, $total);
		}

		return FALSE;
	}

	/**
	 * @param $info
	 * @param $detail
	 * @param $subtotal
	 * @param $discount
	 * @param $total
	 *
	 * @return bool|void
	 */
	private function _sendNotifyStaff(
		$info,
		$detail,
		$subtotal,
		$discount,
		$total){
		$config = Drupal::config(PackingMaterialConfigForm::$config_name);

		$params = [];
		$to     = $config->get('staff_to_email');

		$params['message'] = $config->get('staff_message')['value'];
		$params['from']    = $config->get('staff_from_email');
		$params['subject'] = $config->get('staff_subject');

		if (!empty($to)){
			return $this->_sendNotify($params, $to, $info, $detail, $subtotal,
				$discount, $total);
		}

		return FALSE;
	}

}