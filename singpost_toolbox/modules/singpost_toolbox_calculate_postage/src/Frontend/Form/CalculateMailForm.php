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

/**
 * Class CalculateMailForm
 *
 * @package Drupal\singpost_toolbox_calculate_postage\Frontend\Form
 */
class CalculateMailForm extends FrontendFormBase{

  /**
   * @return string
   */
  public function getFormId(){
    return 'calculate_mail_frontend_form';
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
    if ($position == 'node'){
      $user_submission = $this->_getSubmission($this->getFormId());
    }else{
      $user_submission = [];
    }

    $helper    = new CalculateHelper();
    $countries = $helper->getListCountry();

    $form['#action'] = Url::fromRoute('singpost.toolbox.calculate.singapore.index')->toString();

    $form['#attributes'] = [
      'class' => ['main-form toolbox-form calculate-mail']
    ];

    $form['#attributes']['id'] = 'calculate_mail_frontend_form_' . $position;

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
        'class' => ['row']
      ]
    ];

    $form['row']['col-1'] = [
      '#type'       => 'container',
      '#attributes' => [
        'class' => [
          ($position == 'node') ? 'col-md-6' : 'col-12'
        ]
      ]
    ];

    $form['row']['col-2'] = [
      '#type'       => 'container',
      '#attributes' => [
        'class' => [
          ($position == 'node') ? 'col-md-6' : 'col-12'
        ]
      ]
    ];

    $form['row']['col-2']['sub-row'] = [
      '#type'       => 'container',
      '#attributes' => [
        'class' => ['row']
      ]
    ];

    $form['row']['col-1']['sending_to'] = [
      '#title'         => t('I am sending to *'),
      '#title_display' => ($position == 'node') ? 'before' : 'invisible',
      '#type'          => 'select',
      '#required'      => TRUE,
      '#options'       => $countries,
      '#default_value' => 'SG',
      '#disabled'      => TRUE,
      '#empty_option'  => NULL,
      '#prefix'        => t('<div class="@col">',
        ['@col' => ($position == 'node' ? '' : 'd-none')]),
      '#suffix'        => '</div>',
    ];

    if ($position == 'node'){
      $form['row']['col-2']['sub-row']['title'] = [
        '#prefix' => '<div class="col-12"><div class="font-weight-bold label">',
        '#suffix' => '</div></div>',
        '#markup' => t('My item weighs *')
      ];
    }

    $form['row']['col-2']['sub-row']['weight'] = [
      '#title_display' => 'invisible',
      '#type'          => 'textfield',
      '#required'      => TRUE,
      '#placeholder'   => ($position == 'node') ? t('Eg. 100') : t('My Item Weighs'),
      '#prefix'        => t('<div class="@col">',
        ['@col' => ($position == 'node' ? 'col-6' : 'col-12')]),
      '#suffix'        => '</div>',
      '#default_value' => $user_submission['weight'] ?? '',
      '#maxlength'     => 5,
    ];

    $form['row']['col-2']['sub-row']['unit'] = [
      '#type'          => 'select',
      '#required'      => TRUE,
      '#options'       => $helper::WEIGHT_UNIT,
      '#default_value' => $user_submission['unit'] ?? '',
      '#empty_option'  => NULL,
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
      $form['recaptcha']                  = [
        '#markup' => '<div class="modal fade" tabindex="-1" role="dialog" id="recaptcha-modal-cmail"><div class="modal-dialog modal-dialog-centered"><div class="modal-content recaptcha-modal"><div class="checkbox"><div id="recaptcha-cmail"></div></div></div></div></div>'
      ];
      $form['g-recaptcha-response-cmail'] = [
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
   * @throws \Exception
   */
  public function validateForm(array &$form, FormStateInterface $form_state){
    $sending_to = $form_state->getValue('sending_to');
    $weight     = $form_state->getValue('weight');
    $unit       = $form_state->getValue('unit');

    if (!$sending_to){
      $form_state->setErrorByName('sending_to', t('Please select country.'));
    }elseif ($sending_to != 'SG'){
      $form_state->setErrorByName('sending_to', t('Only select Singapore.'));
    }

    if (!$weight){
      $form_state->setErrorByName('weight', t('Please enter weight.'));
    }elseif (!is_numeric($weight) || (is_numeric($weight) && ($weight <= 0 || (strlen($weight) > 5 && $weight > 0)))){
      $form_state->setErrorByName('weight',
        t('Weight must be a positive number.'));
    }

    if (!$unit){
      $form_state->setErrorByName('unit', t('Please choose unit weight.'));
    }

    // if (class_exists('Protection')){
      try{
        $protection = new Protection('calculate_mail', ['READ_ONLY' => TRUE]);

        if ($protection->status == $protection::CAPTCHA){
          $recaptcha_token = $form_state->getValue('g-recaptcha-response-cmail');
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
   * @return bool|void
   */
  public function submitForm(array &$form, FormStateInterface $form_state){
    parent::submitForm($form, $form_state);

    $session = $this->getRequest()->getSession();
    $package = new CalculatePackageForm();

    if ($session->get($package->getFormId())){
      $session->remove($package->getFormId());
    }

    // if (class_exists('Protection')){
      try{
        new Protection('calculate_mail');
      }catch (Exception $exception){

      }
    // }

    $form_state->setRedirect('singpost.toolbox.calculate.singapore.index');
  }

  /**
   * @return array|int
   */
  public function getResults(){
    $submission = $this->_getSubmission($this->getFormId());

    if ($submission){
      $helper    = new CalculateHelper();
      $data      = [];
      $dimension = [];

      $size = $helper->getSize($helper::DIMENSION_SIZE_PACKAGE);

      if (!empty($size)){
        $dimension = [
          'size'   => $size->size_code,
          'length' => $size->length,
          'width'  => $size->width,
          'height' => $size->height
        ];
      }

      if (!empty($submission['sending_to']) && !empty($submission['weight']) && !empty($submission['unit']) && !empty($dimension)){
        $data = $helper->calculateForSingapore($submission['weight'], $submission['unit'],
          $dimension);

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
