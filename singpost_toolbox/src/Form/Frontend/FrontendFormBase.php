<?php

namespace Drupal\singpost_toolbox\Form\Frontend;


use Drupal;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class FrontendFormBase
 *
 * @package Drupal\singpost_toolbox\Form\Frontend
 */
class FrontendFormBase extends FormBase{

  public $config_recaptcha;

  public function __construct() {
    $this->config_recaptcha = $this->config('simple_recaptcha.config');
  }

  /**
   * @return string
   */
  public function getFormId(){
    return 'frontend_toolbox_form_base';
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return bool
   */
  public function submitForm(array &$form, FormStateInterface $form_state){
    $form_id = $form_state->getValue('form_id');
    $form_state->cleanValues();

    $values = Json::encode($form_state->getValues());

    $request = $this->getRequest();
    $session = $request->getSession();
    $session->set($form_id, $values);

    return TRUE;
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return array
   */
  public function buildForm(array $form, FormStateInterface $form_state){
    return $form;
  }

  /**
   * @param $form_id
   *
   * @return array|mixed
   */
  protected function _getSubmission($form_id){
    $session = $this->getRequest()->getSession();
    $result  = [];

    if ($submission = $session->get($form_id)){
      $result = Json::decode($submission);
    }

    return $result;
  }

  public function clearForm(){
    $session = $this->getRequest()->getSession();
    if($this->getFormId() != 'frontend_locate_us_form'){
      $session->remove($this->getFormId());
    }
    //$session->remove($this->getFormId());
    $session->remove('data_' . $this->getFormId());
  }
}
