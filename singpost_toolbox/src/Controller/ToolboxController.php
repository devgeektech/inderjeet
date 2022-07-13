<?php


namespace Drupal\singpost_toolbox\Controller;


use Drupal;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Controller\ControllerBase;
use Drupal\singpost_protection\Utils\Protection;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class ToolboxController
 *
 * @package Drupal\singpost_toolbox\Controller
 */
class ToolboxController extends ControllerBase{

  /**
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function index(){
    // Drupal::service('libraries.manager')->load('function_protection');
    $protectionSettings = Drupal::service('settings')->get('protection_function') ?? [];

    if (!empty($protectionSettings["TIMEOUT"]) && !ini_get('safe_mode')){
      set_time_limit($protectionSettings["TIMEOUT"]);
    }

    $functions = [
      'track_and_trace'           => 'track_and_trace_frontend_form',
      'calculate_mail'            => 'calculate_mail_frontend_form',
      'calculate_package'         => 'calculate_package_frontend_form',
      'calculate_oversea'         => 'frontend_calculate_by_overseas_form',
      'find_postal_code_landmark' => 'find_postal_code_frontend_form_landmark',
      'find_postal_code_pobox'    => 'find_postal_code_frontend_form_pobox',
      'find_postal_code_street'   => 'find_postal_code_frontend_form_street',
      'locate_us'                 => 'frontend_locate_us_form',
      'redirect_redeliver'        => 'frontend_redirect_redeliver_form',
    ];

    $settings = [
      'READ_ONLY' => TRUE
    ];

    $results = [];

    if ($protectionSettings['DISABLE']){
      foreach ($functions as $func => $id){
        $results += [$id => 1];
      }
    }else{
      $protection = new Protection('', $settings);
      $results    = $protection->getMultipleStatus($functions);
    }

    $results = Json::encode($results);

    return new JsonResponse($results, 200, [], TRUE);
  }
}
