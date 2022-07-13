<?php


namespace Drupal\singpost_toolbox_find_postal_code\Controller;


use Drupal;
use Drupal\Core\Controller\ControllerBase;
use Drupal\singpost_toolbox_find_postal_code\Form\Config\FindPostalCodeConfig;
use Drupal\singpost_toolbox_find_postal_code\Form\Frontend\LandmarkForm;
use Drupal\singpost_toolbox_find_postal_code\Form\Frontend\POBoxForm;
use Drupal\singpost_toolbox_find_postal_code\Form\Frontend\StreetForm;

/**
 * Class FindPostalCodeController
 *
 * @package Drupal\singpost_toolbox_find_postal_code\Controller
 */
class FindPostalCodeController extends ControllerBase{

	private $_active = [
		'street'   => FALSE,
		'landmark' => FALSE,
		'pobox'    => FALSE
	];

	/**
	 * @return array
	 */
	public function index(){
		return [];
	}

	/**
	 * @param $position
	 *
	 * @return array
	 */
	public function buildForm($position){
		$build = [
			'#theme'    => 'singpost_find_postal_code',
			'#attached' => [
				'library' => [
					'singpost_toolbox/toolbox',
					'singpost_toolbox/recaptcha'
				]
			],
			'#cache'    => [
				'max-cache' => 0
			]
		];

		$street   = Drupal::formBuilder()
		                  ->getForm(StreetForm::class, $position);
		$landmark = Drupal::formBuilder()
		                  ->getForm(LandmarkForm::class, $position);
		$pobox    = Drupal::formBuilder()
		                  ->getForm(POBoxForm::class, $position);

		$config     = Drupal::config(FindPostalCodeConfig::$config_name);

		if ($position == 'node'){
			$r_landmark = $this->_getLandmarkResults();
			$r_street   = $this->_getStreetResults();
			$r_pobox    = $this->_getPOBoxResults();

			if (!empty($r_street) && is_array($r_street)){
				usort($r_street, function ($one, $two){
					return $one['PostalCode'] <=> $two['PostalCode'];
				});
			}
		}

		$build['#list_form'] = [
			'street'   => Drupal::service('renderer')->render($street),
			'landmark' => Drupal::service('renderer')->render($landmark),
			'pobox'    => Drupal::service('renderer')->render($pobox),
			'active'   => $this->_active
		];

		$build['#list_result'] = [
			'help'    => [
				'error'          => $config->get('fpc_error_message'),
				'count_street'   => (!empty($r_street) && is_array($r_street)) ? count($r_street) : NULL,
				'count_landmark' => (!empty($r_landmark) && is_array($r_landmark)) ? count($r_landmark) : NULL,
				'count_pobox'    => (!empty($r_pobox) && is_array($r_pobox)) ? count($r_pobox) : NULL,
				'postion'        => ($position == 'node') ? '-node' : '-side'
			],
			'results' => [
				'street'   => $r_street ?? NULL,
				'landmark' => $r_landmark ?? NULL,
				'pobox'    => $r_pobox ?? NULL
			]
		];

		return $build;
	}

	/**
	 * @return array|int|mixed|\SimpleXMLElement|string
	 */
	private function _getStreetResults(){
		$form    = new StreetForm();
		$results = $form->getResults();

		if (is_array($results)){
			$this->_active['street'] = TRUE;
		}

		return $results;
	}

	/**
	 * @return array|int|mixed|\SimpleXMLElement|string
	 */
	private function _getLandmarkResults(){
		$form    = new LandmarkForm();
		$results = $form->getResults();

		if (is_array($results)){
			$this->_active['landmark'] = TRUE;
		}

		return $results;
	}

	/**
	 * @return array|int|mixed|\SimpleXMLElement|string
	 */
	private function _getPOBoxResults(){
		$form    = new POBoxForm();
		$results = $form->getResults();

		if (is_array($results)){
			$this->_active['pobox'] = TRUE;
		}

		return $results;
	}
}