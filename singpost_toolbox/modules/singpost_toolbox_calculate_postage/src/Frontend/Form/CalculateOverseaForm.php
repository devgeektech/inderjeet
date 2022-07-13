<?php


namespace Drupal\singpost_toolbox_calculate_postage\Frontend\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\singpost_toolbox\Form\Frontend\FrontendFormBase;
use Drupal\singpost_toolbox\Helper\Recaptcha;
use Drupal\singpost_toolbox_calculate_postage\Helper\CalculateHelper;
use Exception;
use Drupal\singpost_protection\Utils\Protection;

/**
 * Class CalculateOverseaForm
 *
 * @package Drupal\singpost_toolbox_calculate_postage\Form\Frontend
 */
class CalculateOverseaForm extends FrontendFormBase{

  /**
   * @return string
   */
  public function getFormId(){
    return 'frontend_calculate_by_overseas_form';
  }

  /**
   * @param array $form
   * @param string $position
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return array
   */
  public function buildForm(array $form, FormStateInterface $form_state, $position = 'node'){

    $helper = new CalculateHelper();

    if ($position == 'node'){
      $user_submission = $this->_getSubmission($this->getFormId());
    }else{
      $user_submission = [];
    }

    $list_country = $helper->getListCountry();

    if (!empty($list_country)){
      unset($list_country['SG']);
    }

    $form['#action'] = Url::fromRoute('singpost.toolbox.calculate.overseas.index')->toString();

    $form['#attributes'] = [
      'class' => ['main-form toolbox-form frontend-calculate-overseas ' . ($position == 'node' ? 'node-form' : 'side-form')],
    ];

    $form['#attributes']['id'] = 'calculate_overseas_frontend_form_' . $position;

    $form['#attached'] = [
      'library' => [
        'singpost_toolbox_calculate_postage/sort-table'
      ]
    ];

    $form['#attached'] = [
      'library' => [
        'singpost_toolbox_calculate_postage/sort-table'
      ]
    ];

    $form['row'] = [
      '#type'       => 'container',
      '#attributes' => [
        'class' => ['row'],
      ],
    ];

    $form['row']['left'] = [
      '#type'       => 'container',
      '#attributes' => [
        'class' => [
          ($position == 'node') ? 'col-md-6' : 'col-12'
        ]
      ]
    ];

    $form['row']['right'] = [
      '#type'       => 'container',
      '#attributes' => [
        'class' => [
          ($position == 'node') ? 'col-md-6' : 'col-12'
        ]
      ]
    ];

    $form['row']['right']['child-row'] = [
      '#type'       => 'container',
      '#attributes' => [
        'class' => ['row']
      ]
    ];

    $form['row']['left']['country'] = [
      '#type'          => 'select',
      '#options'       => $list_country,
      '#title'         => $this->t('I am sending to *'),
      '#title_display' => ($position == 'node') ? 'before' : 'invisible',
      '#default_value' => $user_submission['country'] ?? '',
      '#required'      => TRUE,
      '#empty_option'  => ($position == 'node') ? t('Select') : t('I am sending to'),
      '#attributes'    => [
        'class' => ['select2 form-control-lg']
      ]
    ];

    if ($position == 'node'){
      $form['row']['right']['child-row']['title'] = [
        '#prefix' => '<div class="col-12"><div class="font-weight-bold label">',
        '#suffix' => '</div></div>',
        '#markup' => t('My item weighs *')
      ];
    }

    $form['row']['right']['child-row']['weight'] = [
      '#type'          => 'textfield',
      '#title_display' => 'invisible',
      '#default_value' => $user_submission['weight'] ?? '',
      '#maxlength'     => 5,
      '#required'      => TRUE,
      '#placeholder'   => ($position == 'node') ? t('Eg. 100') : t('My Item Weighs'),
      '#prefix'        => t('<div class="@col">',
        ['@col' => ($position == 'node' ? 'col-6' : 'col-12')]),
      '#suffix'        => '</div>',
    ];

    $form['row']['right']['child-row']['unit'] = [
      '#type'          => 'select',
      '#options'       => $helper::WEIGHT_UNIT,
      '#title_display' => 'invisible',
      '#default_value' => $user_submission['unit'] ?? '',
      '#prefix'        => t('<div class="@col">',
        ['@col' => ($position == 'node' ? 'col-6' : 'col-12')]),
      '#suffix'        => '</div>'
    ];

    if ($position == 'node'){
      $form['delivery_link'] = [
        '#markup' => $helper->getDeliveryTimeRateLink()
      ];

      $form['note_link'] = [
        '#markup' => $helper->getNoteLink()
      ];
    }

    $form['actions'] = [
      '#type'       => 'actions',
      '#attributes' => [
        'class' => ['text-lg-right text-center']
      ]
    ];

    $form['actions']['submit'] = [
      '#type'       => 'submit',
      '#value'      => t('Find Services'),
      '#attributes' => [
        'class' => ['btn btn-form-submit']
      ]
    ];

    if ($this->config_recaptcha->get('site_key')){
      $form['recaptcha']                     = [
        '#markup' => '<div class="modal fade" tabindex="-1" role="dialog" id="recaptcha-modal-coversea"><div class="modal-dialog modal-dialog-centered"><div class="modal-content recaptcha-modal"><div class="checkbox"><div id="recaptcha-coversea"></div></div></div></div></div>'
      ];
      $form['g-recaptcha-response-coversea'] = [
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
    $country = $form_state->getValue('country');
    $weight  = $form_state->getValue('weight');

    if (!$country){
      $form_state->setErrorByName('country',
        t('Please select country.'));
    }

    if (!$weight){
      $form_state->setErrorByName('weight', t('Please enter weight.'));
    }elseif (!is_numeric($weight) || (is_numeric($weight) && ($weight <= 0 || (strlen($weight) > 5 && $weight > 0)))){
      $form_state->setErrorByName('weight',
        t('Weight must be a positive number.'));
    }

    // if (class_exists('Protection')){
      try{
        $protection = new Protection('calculate_oversea', ['READ_ONLY' => TRUE]);

        if ($protection->status == $protection::CAPTCHA){
          $recaptcha_token = $form_state->getValue('g-recaptcha-response-coversea');
          $site_key        = $this->config_recaptcha->get('site_key');
          $secret_key      = $this->config_recaptcha->get('secret_key');

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
   * @return \Drupal\Core\Form\FormStateInterface
   */
  public function submitForm(array &$form, FormStateInterface $form_state){
    parent::submitForm($form, $form_state);

    // if (class_exists('Protection')){
      try{
        new Protection('calculate_oversea');
      }catch (Exception $exception){
      }
    // }

    return $form_state->setRedirect('singpost.toolbox.calculate.overseas.index');
  }

  /**
   * @return array|int|mixed|\SimpleXMLElement|string
   */
  public function getResults(){
    $submission = $this->_getSubmission($this->getFormId());

    if ($submission){
      $helper  = new CalculateHelper();
      $data    = [];
      $country = $submission['country'] ?? '';
      $weight  = $submission['weight'] ?? '';
      $unit    = $submission['unit'] ?? '';

      if (!empty($country) && !empty($weight) && !empty($unit)){
        $data = $helper->calculateForOverSea($country, $weight, $unit);

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
