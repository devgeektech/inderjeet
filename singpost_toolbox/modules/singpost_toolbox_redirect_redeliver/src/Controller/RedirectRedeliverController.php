<?php


namespace Drupal\singpost_toolbox_redirect_redeliver\Controller;


use Drupal;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\singpost_toolbox_redirect_redeliver\Form\Config\RedirectRedeliverConfigForm;
use Drupal\singpost_toolbox_redirect_redeliver\Form\Frontend\RedirectRedeliverForm;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class RedirectRedeliverController
 *
 * @package Drupal\singpost_toolbox_redirect_redeliver\Controller
 */
class RedirectRedeliverController extends ControllerBase{

	/**
	 * @return array
	 */
	public function index(){
		return [];
	}

	/**
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 *
	 * @return array
	 */
	public function success(Request $request){
		$session = $request->getSession();
		$form    = new RedirectRedeliverForm();

		$data       = $session->get($form->getFormId());
		$is_success = $session->get('data_is_success');
		$config     = Drupal::config(RedirectRedeliverConfigForm::$config_name);
		$error      = $config->get('rr_error_message');
		$back_url   = Url::fromRoute('singpost.toolbox.redirect_redeliver.index')
		                 ->toString();

		if (!empty($session) && !empty($data = Json::decode($data))){
			return [
				'#theme' => 'singpost_redirect_redeliver_success',
				'#data'  => ['data' => $data, 'error' => $error, 'is_success' => $is_success, 'back_url' => $back_url],
				'#cache' => ['max-age' => 0],
			];
		}

		return $this->redirect($back_url);
	}

}