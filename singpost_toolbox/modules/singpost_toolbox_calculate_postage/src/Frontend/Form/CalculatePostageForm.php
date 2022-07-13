<?php


namespace Drupal\singpost_toolbox_calculate_postage\Frontend\Form;


use Exception;
use Drupal\Core\Url;
use Drupal\Core\Form\FormStateInterface;
use Drupal\singpost_toolbox\Helper\Recaptcha;
use Drupal\singpost_protection\Utils\Protection;
use Drupal\singpost_toolbox\Form\Frontend\FrontendFormBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\singpost_toolbox_calculate_postage\Helper\CalculateHelper;
use Drupal\singpost_toolbox_calculate_postage\Frontend\Form\CalculateMailForm;

/**
 * Class CalculatePackageForm
 *
 * @package Drupal\singpost_toolbox_calculate_postage\Frontend\Form
 */
class CalculatePostageForm extends FrontendFormBase{

  /**
   * @return string
   */
  public function getFormId(){
    return 'calculate_postage_frontend_form';
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
      //$user_submission = [];
      $user_submission = $this->_getSubmission($this->getFormId());
      if($user_submission['sending_to'] == 'SG'){
        $user_submission['location'] = 'local';
      }
      else{
        $user_submission['location'] = 'overseas';
      }
      //$user_side_submission = $this->_getSubmission($this->getFormId());

    $helper    = new CalculateHelper();
    $countries = $helper->getListCountry();
    
    $form['#action'] = Url::fromRoute('singpost.toolbox.calculate.postage.index')->toString();

    $form['#attributes'] = [
      'class' => ['main-form toolbox-form calculate-postage']
    ];

    $form['#attributes']['id'] = 'calculate_postage_frontend_form_' . $position;

    $form['#attached'] = [
      'library' => [
        'singpost_toolbox_calculate_postage/sort-table',
        'singpost_toolbox_calculate_postage/form'
      ]
    ];

    if($position == 'node'){
      $resetval = \Drupal::request()->query->get('reset');
      if($resetval == 1 && $position == 'node'){
        $user_submission = [];
        $this->clearForm();
      }
      $form['row'] = [
        '#type'       => 'container',
        '#attributes' => [
          'class' => ['row'],
          'id' => ($position == 'node') ? 'form-row-calculate-node' : 'form-row-calculate-side'
        ]
      ];
  
      $form['row']['col-1'] = [
        '#type'       => 'container',
        '#attributes' => [
          'class' => [
            ($position == 'node') ? 'col-md-6' : 'col-12 order-2'
          ]
        ]
      ];
      $form['row']['col-1']['form-row'] = [
        '#type'       => 'container',
        '#attributes' => [
          'class' => [
            ($position == 'node') ? 'sgp-calc-postage__row' : ''
          ]
        ]
      ];
      $form['row']['col-1']['weight-row'] = [
        '#type'       => 'container',
        '#attributes' => [
          'class' => [
            ($position == 'node') ? 'sgp-calc-postage__row' : ''
          ]
        ]
      ];
  
      $form['row']['col-2'] = [
        '#type'       => 'container',
        '#attributes' => [
          'class' => [
            ($position == 'node') ? 'sgp-calc-postage__result' : 'col-12 order-1'
          ]
        ]
      ];
      if($user_submission['package_type'] == "mail"){
        $weight_val = $user_submission['weight'];
        $unit_val = $user_submission['weight_unit'];
        $parcel_value = 'Parcel Mail';
      }
      else{
        if($user_submission['location'] == 'overseas'){
          $weight_val = $user_submission['weight'];
          $unit_val = $user_submission['weight_unit'];
        }
        else{
          $weight_val = $user_submission['package_weight'];
          $weight_val = $weight_val / 1000;
          $unit_val = 'kg';
          $parcel_value = 'Parcel Package';
        }
      }
        $sending_to_value = $user_submission['sending_to'];
        $country_value = $user_submission['location'] == 'local' ? 'Singapore' : $sending_to_value;
        $form['row']['col-2']['result-sec'] = [
          '#type'   => 'item',
          '#markup' => t("<div class='sgp-calc-postage__result-box'><div class='sgp-calc-postage__result-box-head'>Your Postage</div>
          <div class='sgp-calc-postage__result-box-row'>
              <div class='sgp-calc-postage__result-box-label'>To:</div>
              <div class='sgp-calc-postage__result-box-value' id='sending_to_js'>$country_value</div>
          </div>
          <div class='sgp-calc-postage__result-box-row'>
              <div class='sgp-calc-postage__result-box-label'>Selected:</div>
              <div class='sgp-calc-postage__result-box-value'>$weight_val $unit_val - <span id='package-val-js'>$parcel_value</span> <br /><strong id='result_cal_api_js'>Basic Package (No Tracking)<br />2 working days</strong></div>
          </div>
          <div class='sgp-calc-postage__result-box-row'>
              <div class='sgp-calc-postage__result-box-label'>Total:</div>
              <div class='sgp-calc-postage__result-box-value sgp-calc-postage__result-box-value--total' id='result_price_api_js'>S$1.50</div>
          </div></div>
          <a href='/shop/packing-materials-post-office' title='Buy a packing material' class='sgp-btn'>
              <span class='sgp-btn__icon'>
                  <svg viewBox='0 0 76 76' fill='none' xmlns='http://www.w3.org/2000/svg'><path d='M36.9608 53.3546L52.6381 37.6773L36.9608 22L34.901 24.1743L48.2896 37.6773L34.901 51.1803L36.9608 53.3546Z'></path><path d='M50.4639 36.0752H23V39.2793H50.4639V36.0752Z'></path></svg>
              </span>Buy a packing material
          </a>
          <a href='/locate-us' title='Locate Us' class='sgp-btn'>
              <span class='sgp-btn__icon'>
              <svg viewBox='0 0 76 76' fill='none' xmlns='http://www.w3.org/2000/svg'><path d='M36.9608 53.3546L52.6381 37.6773L36.9608 22L34.901 24.1743L48.2896 37.6773L34.901 51.1803L36.9608 53.3546Z'></path><path d='M50.4639 36.0752H23V39.2793H50.4639V36.0752Z'></path></svg>
              </span>Locate Us
          </a>"),
          '#attributes'    => [
            'class' => ['d-none'],
          ]
        ];
  
      $form['row']['col-1']['form-row']['location'] = [
        '#title'         => t('Location'),
        '#title_display' => 'invisible',
        '#type'          => 'select',
        '#required'      => TRUE,
        '#options'       => ['local' => 'Local', 'overseas' => 'Overseas'],
        '#default_value' => $user_submission['location'] ?? 'local',
        //'#empty_option'  => t('Select'),
        '#prefix'        => t(
          '<div class="@col">',
          ['@col' => ($position == 'node' ? 'mobile-w-100' : 'd-none')]
        ),
        '#suffix'        => '</div>',
        '#attributes'    => [
          'class' => ['calculate-postage-location sgp-select sgp-select--single'],
        ]
      ];
  
      $form['row']['col-1']['form-row']['sending_to'] = [
        '#title'         => t('I am sending to *'),
        '#title_display' => ($position == 'node') ? 'invisible' : 'invisible',
        '#type'          => 'select',
        '#required'      => TRUE,
        '#options'       => $countries,
        '#default_value' => $user_submission['sending_to'] ?? 'SG',
        //'#empty_option'  => t('Destination Country'),
        // '#disabled'      => TRUE,
        '#prefix'        => t('<div class="@col">',
          ['@col' => ($position == 'node' ? 'mobile-w-100' : 'd-none')]),
        '#suffix'        => '</div>',
        '#states' => [
          'invisible' => [
            ':input[name="location"]' => ['value' => 'local'],
          ],
        ],
        '#attributes'    => [
          'class' => ['calculate-postage-country sgp-select'],
        ]
      ];
  
      $form['row']['col-1']['form-row']['package_type'] = [
        '#title'         => t('Package Type'),
        '#title_display' => 'invisible',
        '#type'          => 'select',
        '#required'      => TRUE,
        '#options'       => ['mail' => 'Mail', 'package' => 'Package'],
        '#default_value' => $user_submission['package_type'] ?? 'mail',
        '#prefix'        => t(
          '<div class="@col">',
          ['@col' => ($position == 'node' ? 'mobile-w-100' : 'd-none')]
        ),
        '#suffix'        => '</div>',
        '#attributes'    => [
          'class' => ['calculate-postage-package-type sgp-select mobile-w-100'],
        ],
        '#states' => [
          'invisible' => [
            ':input[name="location"]' => ['value' => 'overseas'],
          ],
        ],
      ];
  
      $form['row']['col-1']['weight-row']['weight'] = [
        '#title'         => $this->t('My item weighs *'),
        '#title_display' => ($position == 'node') ? 'before' : 'invisible',
        '#type'          => 'textfield',
        //'#required'      => TRUE,
        // '#options'       => $helper::WEIGHT_LIST,
        '#required_error' => t('My item weighs is required'),
        '#placeholder'   => t('Weight'),
        '#default_value' => $user_submission['weight'] ?? '',
        '#maxlength'     => 5,
        '#attributes'    => [
          'class' => ['sgp-input-text'],
        ],
        '#states' => [
          'disabled' => [
              ':input[name="location"]' => ['value' => 'local'],
              'AND',
              ':input[name="package_type"]' => ['value' => 'package'],
          ],
          'invisible' => [
            ':input[name="location"]' => ['value' => 'local'],
            'AND',
            ':input[name="package_type"]' => ['value' => 'package'],
          ],
          'required' => [
            [
              ':input[name="location"]' => ['value' => 'local'],
              'AND',
              ':input[name="package_type"]' => ['value' => 'mail'],
            ],
            'OR',
            [
              ':input[name="location"]' => ['value' => 'overseas'],
            ]
          ],
        ]
      ];
  
      $form['row']['col-1']['package_weight'] = [
        '#title'         => $this->t('My item package weighs'),
        '#title_display' => ($position == 'node') ? 'invisible' : 'invisible',
        '#type'          => 'select',
        //'#required'      => TRUE,
        '#options'       => $helper::WEIGHT_LIST,
        '#default_value' => $user_submission['package_weight'] ?? '',
        '#empty_option'  => t('Please Select @name', [
          '@name' => ($position == 'node') ? 'Weight' : 'Weight'
        ]),
        '#prefix'        => t(
          '<div class="@col">',
          ['@col' => ($position == 'node' ? 'mobile-w-100' : 'd-none')]
        ),
        '#suffix'        => '</div>',
        '#attributes'    => [
          'class' => ['sgp-select sgp-select--single sgp-calc-postage__row mobile-w-100'],
        ],
        '#states' => [
          'enabled' => [
              ':input[name="location"]' => ['value' => 'local'],
              'AND',
              ':input[name="package_type"]' => ['value' => 'package'],
          ],
          'visible' => [
            ':input[name="location"]' => ['value' => 'local'],
            'AND',
            ':input[name="package_type"]' => ['value' => 'package'],
          ],
          'required' => [
              ':input[name="location"]' => ['value' => 'local'],
              'AND',
              ':input[name="package_type"]' => ['value' => 'package'],
          ],
        ]
      ];
      $form['row']['col-1']['weight-row']['weight_unit'] = [
        '#title'         => $this->t('Weight Unit'),
        '#title_display' => 'invisible',
        //'#after_build' => array('custom_process_weight_unit'),
        '#type'          => 'radios',
        '#required'      => TRUE,
        '#options'       => ['g' => 'GRAMS', 'kg' => 'KILOGRAMS'],
        '#default_value' => $user_submission['weight_unit'] ?? 'g',
        '#attributes'    => [
          'class' => ['btn-check custm_weight_radio'],
        ],
        '#states' => [
          'disabled' => [
              ':input[name="location"]' => ['value' => 'local'],
              'AND',
              ':input[name="package_type"]' => ['value' => 'package'],
          ],
          'invisible' => [
            ':input[name="location"]' => ['value' => 'local'],
            'AND',
            ':input[name="package_type"]' => ['value' => 'package'],
          ],
        ]
      ];
  
      $form['row']['col-1']['form-row']['dimension'] = [
        '#title'         => t('Dimension'),
        '#type'          => 'select',
        '#title_display' => ($position == 'node') ? 'invisible' : 'invisible',
        '#required'      => FALSE,
        '#options'       => $helper->getListDimension(),
        '#default_value' => $user_submission['dimension'] ?? '',
        '#empty_option'  => t('Please Select @name', [
          '@name' => ($position == 'node') ? 'Dimension' : 'Dimension'
        ]),
        '#attributes'    => [
          'class' => [($position == 'node') ? 'sgp-select sgp-select--single mb-0' : 'select2 form-control-lg'],
        ],
        '#states' => [
          'required' => [
            [
              ':input[name="location"]' => ['value' => 'local'],
              'AND',
              ':input[name="package_type"]' => ['value' => 'package'],
            ]
          ],
          'invisible' => [
            [
              ':input[name="location"]' => ['value' => 'local'],
              'AND',
              ':input[name="package_type"]' => ['value' => 'mail'],
            ],
            'OR',
            [
              ':input[name="location"]' => ['value' => 'overseas'],
            ]
          ],
        ],
      ];
  
      if ($position == 'node'){
        $form['row']['col-1']['delivery_link'] = [
          '#markup' => $helper->getDeliveryTimeRateLink(),
        ];
  
        $form['row']['col-1']['note_link'] = [
          '#markup' => $helper->getNoteLink(),
        ];
      }
  
      $form['row']['col-1']['actions'] = [
        '#type'       => 'actions',
        '#attributes' => [
          'class' => ['text-lg-left']
        ]
      ];
  
      $form['row']['col-1']['actions']['submit'] = [
        '#type'       => 'submit',
        '#value'      => t('Calculate Now'),
        '#attributes' => [
          'class' => ['btn btn-form-submit sgp-link-btn sgp-link-btn--box span-wrapper']
        ],
      ];
  
    }
    elseif($position == 'side'){
      $form['row'] = [
        '#type'       => 'container',
        '#attributes' => [
          'class' => ['track-trace-sec__tab-cont calculate-postage-side-form-home']
        ]
      ];
     $form['row']['location'] = [
        '#title'         => t('Location'),
        '#title_display' => 'invisible',
        '#type'          => 'select',
        '#required'      => TRUE,
        '#options'       => ['local' => 'Local', 'overseas' => 'Overseas'],
        '#default_value' => $user_submission['sending_to'] == 'SG' ? 'local' : 'overseas',
        '#prefix'        => t(
          '<div class="@col">',
          ['@col' => ($position == 'side' ? 'd-none' : 'd-none')]
        ),
        '#suffix'        => '</div>',
        '#attributes'    => [
          'class' => ['calculate-postage-location sgp-select sgp-select--single'],
        ]
      ];

      $form['row']['sending_to'] = [
        '#title'         => t('I am sending to *'),
        '#title_display' => ($position == 'side') ? 'invisible' : 'invisible',
        '#type'          => 'select',
        '#required'      => TRUE,
        '#options'       => $countries,
        '#default_value' => $user_submission['sending_to'] ?? 'SG',
        // '#disabled'      => TRUE,
        '#attributes'    => [
          'class' => ['calculate-postage-country sgp-select'],
        ]
      ];


      $form['row']['package_type'] = [
        '#title'         => t('Package Type'),
        '#title_display' => 'invisible',
        '#type'          => 'select',
        '#required'      => TRUE,
        '#options'       => ['mail' => 'Mail', 'package' => 'Package'],
        '#default_value' => $user_submission['package_type'] ?? 'mail',
        '#attributes'    => [
          'class' => ['calculate-postage-package-type sgp-select'],
        ],
        '#states' => [
          'visible' => [
            ':input[name="sending_to"]' => ['value' => 'SG'],
          ]
        ],
      ];

      $form['row']['demo-text']['weight'] = [
        '#title'         => $this->t('My item weighs *'),
        '#title_display' => ($position == 'side') ? 'before' : 'invisible',
        '#type'          => 'textfield',
        '#placeholder'    =>t('Weight'),
        '#required_error' => t('My item weighs is required'),
        //'#required'      => TRUE,
        // '#options'       => $helper::WEIGHT_LIST,
        '#default_value' => $user_submission['weight'] ?? '',
        '#maxlength'     => 5,
        '#attributes'    => [
          'class' => ['sgp-input-text'],
        ],
        '#states' => [
          'disabled' => [
              ':input[name="sending_to"]' => ['value' => 'SG'],
              'AND',
              ':input[name="package_type"]' => ['value' => 'package'],
          ],
          'invisible' => [
            ':input[name="sending_to"]' => ['value' => 'SG'],
            'AND',
            ':input[name="package_type"]' => ['value' => 'package'],
          ],
          'required' => [
            [
              ':input[name="sending_to"]' => ['value' => 'SG'],
              'AND',
              ':input[name="package_type"]' => ['value' => 'mail'],
            ],
            'OR',
            [
              ':input[name="sending_to"]' => ['!value' => 'SG'],
            ]
          ],
        ]
        
      ];

      $form['row']['demo-text']['#prefix'] = '<div class="only-tab">';
      $form['row']['demo-text']['#suffix'] = '</div>';

      $form['row']['demo-text']['weight_unit'] = [
        '#title'         => $this->t('Weight Unit'),
        '#title_display' => 'invisible',
        //'#after_build' => array('custom_process_weight_unit'),
        '#type'          => 'radios',
        '#required'      => TRUE,
        '#options'       => ['g' => 'Grams', 'kg' => 'Kilograms'],
        '#default_value' => $user_submission['weight_unit'] ?? 'g',
        '#attributes'    => [
          'class' => ['btn-check custm_weight_radio'],
        ],
        '#states' => [
          'disabled' => [
              ':input[name="sending_to"]' => ['value' => 'SG'],
              'AND',
              ':input[name="package_type"]' => ['value' => 'package'],
          ],
          'invisible' => [
            ':input[name="sending_to"]' => ['value' => 'SG'],
            'AND',
            ':input[name="package_type"]' => ['value' => 'package'],
          ],
        ]
      ];

      $form['row']['dimension'] = [
        '#title'         => t('Dimension'),
        '#type'          => 'select',
        '#title_display' => ($position == 'side') ? 'invisible' : 'invisible',
        '#required'      => FALSE,
        '#options'       => $helper->getListDimension(),
        '#default_value' => $user_submission['dimension'] ?? '',
        '#empty_option'  => t('Please Select @name', [
          '@name' => ($position == 'side') ? 'Dimension' : 'Dimension'
        ]),
        '#attributes'    => [
          'class' => [($position == 'side') ? 'sgp-select sgp-select--single' : 'select2 form-control-lg'],
        ],
        '#states' => [
          'required' => [
            [
              ':input[name="sending_to"]' => ['value' => 'SG'],
              'AND',
              ':input[name="package_type"]' => ['value' => 'package'],
            ]
          ],
          'invisible' => [
            [
              ':input[name="sending_to"]' => ['value' => 'SG'],
              'AND',
              ':input[name="package_type"]' => ['value' => 'mail'],
            ],
            'OR',
            [
              ':input[name="sending_to"]' => ['!value' => 'SG'],
            ]
          ],
        ],
      ];

      $form['row']['package_weight'] = [
        '#title'         => $this->t('My item package weighs'),
        '#title_display' => ($position == 'side') ? 'invisible' : 'invisible',
        '#type'          => 'select',
        //'#required'      => TRUE,
        '#options'       => $helper::WEIGHT_LIST,
        '#default_value' => $user_submission['package_weight'] ?? '',
        '#empty_option'  => t('Please Select @name', [
          '@name' => ($position == 'side') ? 'Weight' : 'Weight'
        ]),
        '#attributes'    => [
          'class' => ['sgp-select sgp-select--single sgp-calc-postage__row'],
        ],
        '#states' => [
          'enabled' => [
              ':input[name="sending_to"]' => ['value' => 'SG'],
              'AND',
              ':input[name="package_type"]' => ['value' => 'package'],
          ],
          'visible' => [
            ':input[name="sending_to"]' => ['value' => 'SG'],
            'AND',
            ':input[name="package_type"]' => ['value' => 'package'],
          ],
          'required' => [
              ':input[name="sending_to"]' => ['value' => 'SG'],
              'AND',
              ':input[name="package_type"]' => ['value' => 'package'],
          ],
        ]
      ];

      $form['row']['actions'] = [
        '#type'       => 'actions',
        '#attributes' => [
          'class' => ['text-lg-left text-center']
        ]
      ];
  
      $form['row']['actions']['submit'] = [
        '#type'       => 'submit',
        '#value'      => t('Calculate Now'),
        '#attributes' => [
          'class' => ['btn btn-form-submit sgp-link-btn sgp-link-btn--box span-wrapper']
        ],
      ];

      
    }
    
    if ($this->config_recaptcha->get('site_key')){
      $form['recaptcha']                     = [
        '#markup' => '<div class="modal fade recaptcha-fix" tabindex="-1" role="dialog" id="recaptcha-modal-cpackage"><div class="modal-dialog modal-dialog-centered"><div class="modal-content recaptcha-modal"><div class="checkbox"><div id="recaptcha-cpackage"></div></div></div></div></div>'
      ];
      $form['g-recaptcha-response-cpackage'] = [
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

    $location   =   $form_state->getValue('location');
    $sending_to =   $form_state->getValue('sending_to');
    $pac_weight =   $form_state->getValue('package_weight');
    $weight     =   $form_state->getValue('weight');
    $package_type = $form_state->getValue('package_type');

    if($sending_to == 'SG'){
      $location   = 'local';
    }
    else{
      $location   = 'overseas';
    }

    if($location == 'local' && $package_type == 'package'){
      if((empty($weight)) && (!empty($pac_weight))){
        $weight = $pac_weight;
      }
    }
    

    //$dimension  = $form_state->getValue('dimension');

    if (!$sending_to){
      $form_state->setErrorByName('sending_to', t('Please select country.'));
    }
    elseif ($location == 'local') {
      if ($sending_to != 'SG'){
        $form_state->setErrorByName('sending_to', t('Only select Singapore.'));
      }
    }

    if (!$weight){
      $form_state->setErrorByName('weight', t('Please enter weight.'));
    }
    elseif (!is_numeric($weight) || (is_numeric($weight) && ($weight <= 0 || (strlen($weight) > 5 && $weight > 0)))){
      $form_state->setErrorByName('weight',
        t('Weight must be a positive number.'));
    }

    // if (!$dimension){
    //   $form_state->setErrorByName('dimension', t('Please select dimension.'));
    // }

    // if (class_exists('Protection')){
      try{
        $protection = new Protection('calculate_postage', ['READ_ONLY' => TRUE]);

        if ($protection->status == $protection::CAPTCHA){
          $recaptcha_token = $form_state->getValue('g-recaptcha-response-cpackage');

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
    // }

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

    $session = $this->getRequest()->getSession();
    $mail    = new CalculateMailForm();

    if ($session->get($mail->getFormId())){
      $session->remove($mail->getFormId());
    }

    // if (class_exists('Protection')){
      try{
        new Protection('calculate_postage');
      }catch (Exception $exception){
      }
    // }

    $form_state->setRedirect('singpost.toolbox.calculate.postage.index');
  }

  /**
   * @return array|int
   */
  public function getResults(){
    $submission = $this->_getSubmission($this->getFormId());
    $resetval = \Drupal::request()->query->get('reset');
    if($resetval == 1){
      $submission = '';
      $data = '';
      return NULL;
    }
    if($submission['sending_to'] == 'SG'){
      $submission['location'] = 'local';
    }
    else{
      $submission['location'] = 'overseas';
    }
    //echo "<pre>";
    //print_r($submission);
    if(($submission['location'] == 'local') && ($submission['package_type'] == 'package') && (!empty($submission['package_weight']))){
      $submission['weight'] = $submission['package_weight'];
      $submission_package_weight = $submission['package_weight'];
      $submission_package_weight_unit = 'g';
    }
    if ($submission){
      $helper    = new CalculateHelper();
      $data      = [];
      $dimension = [];



      if (!empty($submission['sending_to']) && !empty($submission['weight']) && !empty($submission['weight_unit'])) {
        if ($submission['location'] == 'local') {
          if ($submission['package_type'] == 'package') {
            if (!empty($submission['dimension'])) {
              $size = $helper->getSize($submission['dimension']);

              if (!empty($size)) {
                $dimension = [
                  'size'   => $size->size_code,
                  'length' => $size->length,
                  'width'  => $size->width,
                  'height' => $size->height
                ];
              }
              $data = $helper->calculateForSingapore($submission_package_weight, $submission_package_weight_unit, $dimension);
            }
          }
          else {
            $size = $helper->getSize($helper::DIMENSION_SIZE_PACKAGE);
            if (!empty($size)) {
              $dimension = [
                'size'   => $size->size_code,
                'length' => $size->length,
                'width'  => $size->width,
                'height' => $size->height
              ];
            }
            $data = $helper->calculateForSingapore($submission['weight'], $submission['unit'], $dimension);
          }
        }
        else {
          $data = $helper->calculateForOverSea($submission['sending_to'], $submission['weight'], $submission['weight_unit']);
        }


        if (!empty($data)){
          $data = $helper->formatData($data);
        }
      }

      return $data;
    }

    return NULL;
  }

  /**
   * @return bool
   */
  public function hasSubmission(){
    $submission = $this->_getSubmission($this->getFormId());

    if ($submission){
      return TRUE;
    }

    return FALSE;
  }
}