<?php

namespace Drupal\singpost_toolbox_calculate_postage\Plugin\Block;

use Drupal;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\singpost_toolbox_calculate_postage\Helper\CalculateHelper;
use Drupal\singpost_toolbox_calculate_postage\Frontend\Form\CalculateMailForm;
use Drupal\singpost_toolbox_calculate_postage\Frontend\Form\CalculateExpressForm;
use Drupal\singpost_toolbox_calculate_postage\Frontend\Form\CalculateOverseaForm;
use Drupal\singpost_toolbox_calculate_postage\Frontend\Form\CalculatePackageForm;
use Drupal\singpost_toolbox_calculate_postage\Frontend\Form\CalculatePostageForm;

/**
 * Provides a 'Calculate Postage Results' Block.
 *
 * @Block(
 *   id = "calculate_postage_block",
 *   admin_label = @Translation("Calculate Postage Results"),
 *   category = @Translation("Calculate Postage Results"),
 * )
 */
class CalculatePostageBlock extends BlockBase implements ContainerFactoryPluginInterface{

	/**
	 * @return array
	 */
	public function build(){
		$country           = $this->configuration['country'];
		$content_overseas  = $this->configuration['overseas']['value'];
		$content_singapore = $this->configuration['singapore']['value'];
		$form_express      = '';
		$helper            = new CalculateHelper();


		if ($country == 'combined') {
			$data    = $this->_getCombinedCountryResult();
			$form_express = Drupal::formBuilder()->getForm(CalculateExpressForm::class);
			$content = '';
		}
		elseif ($country == 'overseas'){
			$data    = $this->_getOverseaResult();
			$content = $content_overseas;
		}
		else{
			$mail_result    = $this->_getMailResult();
			$package_result = $this->_getPackagelResult();

			if (!empty($mail_result)){
				$data = $mail_result;
			}

			if (!empty($package_result)){
				$data         = $package_result;
				$form_express = Drupal::formBuilder()->getForm(CalculateExpressForm::class);
			}
			$content = $content_singapore;
		}

		$result        = $data ?? NULL;
		$maximum_price = $helper->getMaximumPrice($result);
		$tooltips      = $helper->getTooltip();

		$build = [
			'#theme'         => 'singpost_calculate_result',
			'#cache'         => ['max-age' => 0],
			'#data'          => $result,
			'#tooltips'      => $tooltips,
			'#content'       => $content,
			'#error_message' => $helper->getErrorMessage(),
			'#form'          => $form_express,
			'#maximum_price' => $maximum_price
		];

		return $build;
	}


	/**
	 * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
	 * @param array $configuration
	 * @param string $plugin_id
	 * @param mixed $plugin_definition
	 *
	 * @return \Drupal\Core\Plugin\ContainerFactoryPluginInterface|static
	 */
	public static function create(
		ContainerInterface $container,
		array $configuration,
		$plugin_id,
		$plugin_definition){
		return new static($configuration, $plugin_id, $plugin_definition);
	}

	/**
	 * @return array
	 */
	private function _getCountry(){
		return [
			'overseas' => 'Overseas',
			'local' => 'Singapore',
			'combined' => 'Combined',
		];
	}

	/**
	 * @return array|int|null
	 */
	private function _getMailResult(){
		$mail = new CalculateMailForm();

		return $mail->getResults();
	}

	/**
	 * @return array|int|null
	 */
	private function _getPackagelResult(){
		$package = new CalculatePackageForm();

		return $package->getResults();
	}

	/**
	 * @return array|int|null
	 */
	private function _getCombinedCountryResult(){
		$package = new CalculatePostageForm();

		return $package->getResults();
	}

	/**
	 * @return array|int|mixed|\SimpleXMLElement|string|null
	 */
	private function _getOverseaResult(){
		$oversea = new CalculateOverseaForm();

		return $oversea->getResults();
	}

	/**
	 * @return array
	 */
	public function defaultConfiguration(){
		return [
			'label_display' => TRUE,
			'country'       => ''
		];
	}

	/**
	 * @param $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 *
	 * @return array|void
	 */
	public function blockForm($form, FormStateInterface $form_state){
		$form['country'] = [
			'#type'          => 'select',
			'#title'         => $this->t('Country'),
			'#description'   => $this->t('Select the calculate that will be enabled in the frontend.'),
			'#default_value' => $this->configuration['country'] ?? '',
			'#options'       => static::_getCountry()
		];

		$form['overseas'] = [
			'#type'          => 'text_format',
			'#title'         => $this->t('Overseas Table'),
			'#default_value' => $this->configuration['overseas']['value'] ?? '',
		];

		$form['singapore'] = [
			'#type'          => 'text_format',
			'#title'         => $this->t('Singapore Table'),
			'#default_value' => $this->configuration['singapore']['value'] ?? '',
		];

		return $form;
	}

	/**
	 * @param $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 */
	public function blockSubmit($form, FormStateInterface $form_state){
		$this->configuration['country']   = $form_state->getValue('country');
		$this->configuration['overseas']  = $form_state->getValue('overseas');
		$this->configuration['singapore'] = $form_state->getValue('singapore');
	}
}
