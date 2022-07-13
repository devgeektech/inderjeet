<?php


namespace Drupal\singpost_toolbox_locate_us\Frontend\Controller;

use Drupal;
use Drupal\Core\Controller\ControllerBase;
use Drupal\singpost_toolbox_locate_us\Form\Config\LocateUsConfigForm;
use Drupal\singpost_toolbox_locate_us\Frontend\Form\LocateUsForm;
use Drupal\singpost_toolbox_locate_us\Model\LocateUsType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class LocateUsController
 *
 * @package Drupal\singpost_toolbox_locate_us\Frontend\Controller
 */
class LocateUsController extends ControllerBase{

	/**
	 * @return mixed
	 */
	public function buildForm(){
		$form         = new LocateUsForm();
		$data_request = $form->getParams();

		$form = Drupal::formBuilder()
		              ->getForm(LocateUsForm::class);

		$model               = new LocateUsType();
		$build['#theme']     = 'singpost_locate_us_index';
		$build['#cache']     = ['max-age' => 0];
		$build['#list_type'] = $model->getTypes();
		$build['#form']      = $form;
		$build['#icon']      = $model->tmp_icon;
		$build['#id']        = $data_request['locate-us-type'] ?? NULL;

		$build['#attached'] = [
			'library' => [
				'singpost_toolbox_locate_us/google_map',
				'singpost_toolbox_locate_us/form-locate-us',
				'singpost_toolbox_locate_us/map-locate-us',
				'singpost_toolbox/toolbox',
				'singpost_toolbox/recaptcha'
			],
		];

		return $build;
	}

	/**
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 *
	 * @return \Symfony\Component\HttpFoundation\JsonResponse
	 */
	public function getMapData(Request $request){
		$session  = $request->getSession();
		$form     = new LocateUsForm();
		$data_map = $form->getResults();
		$setting      = Drupal::config(LocateUsConfigForm::$config_name);
		$support_card = $setting->get('locate_us_support_card_popstation');
		//echo "<pre>";
		//print_r($data_map);
		//echo "</pre>";
		$session->remove('locate_us_get_method');

		$return = [
			'icon'     => $data_map['icon'] ?? NULL,
			'marker'   => $data_map['marker'] ?? NULL,
			'data_map' => $data_map['results'] ?? [],
			'place_id' => $data_map['place_id'] ?? NULL,
			'keyword'  => $data_map['keyword'] ?? NULL,
			'url'      => $data_map['url'] ?? FALSE,
			'support_card' => $support_card ? $support_card['value'] : ''
		];

		return new JsonResponse($return);
	}
}