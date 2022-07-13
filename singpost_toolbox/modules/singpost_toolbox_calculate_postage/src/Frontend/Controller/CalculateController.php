<?php


namespace Drupal\singpost_toolbox_calculate_postage\Frontend\Controller;


use Drupal;
use Drupal\Core\Controller\ControllerBase;
use Drupal\singpost_toolbox_calculate_postage\Helper\CalculateHelper;
use Drupal\singpost_toolbox_calculate_postage\Frontend\Form\CalculateMailForm;
use Drupal\singpost_toolbox_calculate_postage\Frontend\Form\CalculateOverseaForm;
use Drupal\singpost_toolbox_calculate_postage\Frontend\Form\CalculatePackageForm;
use Drupal\singpost_toolbox_calculate_postage\Frontend\Form\CalculatePostageForm;

/**
 * Class CalculateController
 *
 * @package Drupal\singpost_toolbox_calculate_postage\Frontend\Controller
 */
class CalculateController extends ControllerBase{

	private $_active = [
		'mail'     => FALSE,
		'package'  => FALSE,
		'overseas' => FALSE,
	];

	/**
	 * @return array
	 */
	public function calculateSingapore(){
		return [];
	}

	/**
	 * @return array
	 */
	public function calculatePostage(){
		return [];
	}

	/**
	 * @return array
	 */
	public function calculateOverseas(){
		return [];
	}

	/**
	 * @param $position
	 *
	 * @return array
	 */
	public function buildCalculateFormOverseas($position = 'node'){
		$helper = new CalculateHelper();
		$links  = $helper->getLinkMenuTab(FALSE);

		$overseas = Drupal::formBuilder()->getForm(CalculateOverseaForm::class, $position);
		$active   = $this->_checkFormActive();

		return [
			'#theme'    => ($position == 'node') ? 'singpost_calculate_wrapper_node' : 'singpost_calculate_overseas',
			'#forms'    => [
				'overseas' => Drupal::service('renderer')->render($overseas)
			],
			'#help'     => [
				'position' => $position,
				'links'    => $links,
				'local'    => FALSE,
				'active'   => $active,
			],
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
	}

	/**
	 * @param string $position
	 *
	 * @return array
	 */
	public function buildCalculateFormLocal($position = 'node'){
		$helper = new CalculateHelper();

		$mail    = Drupal::formBuilder()->getForm(CalculateMailForm::class, $position);
		$package = Drupal::formBuilder()->getForm(CalculatePackageForm::class, $position);
		$active  = $this->_checkFormActive();
		$links   = $helper->getLinkMenuTab();

		return [
			'#theme'    => ($position == 'node') ? 'singpost_calculate_wrapper_node' : 'singpost_calculate_local',
			'#forms'    => [
				'mail'    => Drupal::service('renderer')->render($mail),
				'package' => Drupal::service('renderer')->render($package)
			],
			'#help'     => [
				'position' => $position,
				'active'   => $active,
				'links'    => $links,
				'local'    => TRUE
			],
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
	}

	/**
	 * @param string $position
	 *
	 * @return array
	 */
	public function buildCalculateFormCombined($position = 'node'){
		$helper = new CalculateHelper();

		$combined = Drupal::formBuilder()->getForm(CalculatePostageForm::class, $position);
		$active  = $this->_checkFormActive();
		$links   = $helper->getLinkMenuTab();

		return [
			'#theme'    => ($position == 'node') ? 'singpost_calculate_combined_wrapper_node' : '',
			'#forms'    => [
				'combined' => Drupal::service('renderer')->render($combined),
			],
			'#help'     => [
				'position' => $position,
				'active'   => $active,
				'links'    => $links,
				'local'    => TRUE
			],
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
	}

	public function buildSideForm($position = 'side'){
		$helper = new CalculateHelper();

		$combined = Drupal::formBuilder()->getForm(CalculatePostageForm::class, $position);
		$active  = $this->_checkFormActive();
		$links   = $helper->getLinkMenuTab();

		return [
			'#theme'    => ($position == 'side') ? 'singpost_calculate_side' : '',
			'#forms'    => [
				'combined' => Drupal::service('renderer')->render($combined),
			],
			'#help'     => [
				'position' => $position,
				'active'   => $active,
				'links'    => $links,
				'local'    => TRUE
			],
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
	}

	/**
	 * @return array
	 */
	/* public function buildSideForm(){
		$local_form   = $this->buildCalculateFormLocal('side');
		$oversea_form = $this->buildCalculateFormOverseas('side');

		return [
			'#theme' => 'singpost_calculate_side',
			'#forms' => [
				'local'    => $local_form,
				'overseas' => $oversea_form
			]
		];
	} */

	/**
	 * @return array
	 */
	private function _checkFormActive(){
		$mail    = new CalculateMailForm();
		$package = new CalculatePackageForm();
		$oversea = new CalculateOverseaForm();
		$combined = new CalculatePostageForm();

		return $this->_active = [
			'mail'     => $mail->hasSubmission(),
			'package'  => $package->hasSubmission(),
			'overseas' => $oversea->hasSubmission(),
			'combined' => $combined->hasSubmission(),
		];
	}
}
