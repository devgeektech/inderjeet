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
class CalculatePackageForm extends FrontendFormBase{

  /**
   * @return string
   */
  public function getFormId(){
    return 'calculate_package_frontend_form';
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
      'class' => ['main-form toolbox-form calculate-package']
    ];

    $form['#attributes']['id'] = 'calculate_package_frontend_form_' . $position;

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
          ($position == 'node') ? 'col-md-6' : 'col-12 order-2'
        ]
      ]
    ];

    $form['row']['col-2'] = [
      '#type'       => 'container',
      '#attributes' => [
        'class' => [
          ($position == 'node') ? 'col-md-6' : 'col-12 order-1'
        ]
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
      '#prefix'        => t('<div class="@col">',
        ['@col' => ($position == 'node' ? '' : 'd-none')]),
      '#suffix'        => '</div>',
    ];

    $form['row']['col-1']['weight'] = [
      '#title'         => t('My item weighs *'),
      '#title_display' => ($position == 'node') ? 'before' : 'invisible',
      '#type'          => 'select',
      '#required'      => TRUE,
      '#options'       => $helper::WEIGHT_LIST,
      '#default_value' => $user_submission['weight'] ?? '',
      '#empty_option'  => t('Please Select @name', [
        '@name' => ($position == 'node') ? '' : 'Weight'
      ]),
      '#attributes'    => [
        'class' => [($position == 'node') ? '' : 'select2 form-control-lg'],
      ]
    ];

    $form['row']['col-2']['dimension'] = [
      '#title'         => t('Dimension *'),
      '#type'          => 'select',
      '#title_display' => ($position == 'node') ? 'before' : 'invisible',
      '#required'      => TRUE,
      '#options'       => $helper->getListDimension(),
      '#default_value' => $user_submission['dimension'] ?? '',
      '#empty_option'  => t('Please Select @name', [
        '@name' => ($position == 'node') ? '' : 'Dimension'
      ]),
      '#attributes'    => [
        'class' => [($position == 'node') ? '' : 'select2 form-control-lg'],
      ]
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
      ],
    ];

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
    $sending_to = $form_state->getValue('sending_to');
    $weight     = $form_state->getValue('weight');
    $dimension  = $form_state->getValue('dimension');

    if (!$sending_to){
      $form_state->setErrorByName('sending_to', t('Please select country.'));
    }elseif ($sending_to != 'SG'){
      $form_state->setErrorByName('sending_to', t('Only select Singapore.'));
    }

    if (!$weight){
      $form_state->setErrorByName('weight', t('Please enter weight.'));
    }

    if (!$dimension){
      $form_state->setErrorByName('dimension', t('Please select dimension.'));
    }

    // if (class_exists('Protection')){
      try{
        $protection = new Protection('calculate_package', ['READ_ONLY' => TRUE]);

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
        new Protection('calculate_package');
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

      $size = $helper->getSize($submission['dimension']);

      if (!empty($size)){
        $dimension = [
          'size'   => $size->size_code,
          'length' => $size->length,
          'width'  => $size->width,
          'height' => $size->height
        ];
      }

      if (!empty($submission['sending_to'] && !empty($submission['weight']) && !empty($dimension))){
        $data = $helper->calculateForSingapore($submission['weight'], 'g', $dimension);

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
