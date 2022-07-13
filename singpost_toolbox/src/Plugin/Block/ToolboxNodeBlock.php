<?php


namespace Drupal\singpost_toolbox\Plugin\Block;

use Drupal;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\singpost_toolbox_calculate_postage\Frontend\Controller\CalculateController;
use Drupal\singpost_toolbox_find_postal_code\Controller\FindPostalCodeController;
use Drupal\singpost_toolbox_locate_us\Frontend\Form\LocateUsForm;
use Drupal\singpost_toolbox_redirect_redeliver\Form\Frontend\RedirectRedeliverForm;
use Drupal\singpost_toolbox_track_and_trace\Frontend\Form\TrackAndTraceForm;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Toolbox Node' Block.
 *
 * @Block(
 *   id = "toolbox_node_block",
 *   admin_label = @Translation("Toolbox node block"),
 *   category = @Translation("SingPost Toolbox"),
 * )
 */
class ToolboxNodeBlock extends BlockBase implements ContainerFactoryPluginInterface{

	/**
	 * @return array
	 */
	public function build(){
		$build = [
			'#theme'    => 'singpost_toolbox_node',
			'#cache'    => ['max-age' => 0],
			'#attached' => [
				'library' => ['singpost_toolbox/toolbox']
			]
		];

		$tools   = static::getTools();
		$form_id = $this->configuration['tool'];

		$current_path = Drupal::service('path.current')->getPath();
		$alias        = Drupal::service('path_alias.manager')
		                      ->getAliasByPath($current_path);

		$control_cal = new CalculateController();

		$calculate = [
			'local'    => [
				'url'  => Url::fromRoute('singpost.toolbox.calculate.singapore.index')
				             ->toString(),
				'form' => $control_cal->buildCalculateFormLocal()
			],
			'overseas' => [
				'url'  => Url::fromRoute('singpost.toolbox.calculate.overseas.index')
				             ->toString(),
				'form' => $control_cal->buildCalculateFormOverseas()
			],
			'combined' => [
				'url'  => Url::fromRoute('singpost.toolbox.calculate.postage.index')
				             ->toString(),
				'form' => $control_cal->buildCalculateFormCombined()
			]
		];

		switch ($form_id){
			case 'singpost.toolbox.find_postal_code.index':
				$controller     = new FindPostalCodeController();
				$theme          = $controller->buildForm('node');
				$build['#form'] = $theme;

				break;

			case 'singpost.toolbox.calculate_postage.index':
				if (strcmp($alias, $calculate['combined']['url']) == 0) {
					$build['#form'] = $calculate['combined']['form'];
				}

				if (strcmp($alias, $calculate['local']['url']) == 0){
					$build['#form'] = $calculate['local']['form'];
				}

				if (strcmp($alias, $calculate['overseas']['url']) == 0){
					$build['#form'] = $calculate['overseas']['form'];
				}

				break;

			default:
				if (class_exists($tools[$form_id]['form_class'])){
					$form = Drupal::formBuilder()
					              ->getForm($tools[$form_id]['form_class']);

					$build['#form'] = Drupal::service('renderer')->render($form);
				}

				break;
		}

		return $build;
	}

	/**
	 * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
	 * @param array $configuration
	 * @param string $plugin_id
	 * @param mixed $plugin_definition
	 *
	 * @return \Drupal\Core\Plugin\ContainerFactoryPluginInterface|\Drupal\singpost_toolbox\Plugin\Block\ToolboxNodeBlock
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
	public static function getTools(){
		return [
			'singpost.toolbox.track_and_trace.index'    => [
				'form_label' => 'Track & Trace',
				'form_icon'  => 'track-items',
				'form_class' => TrackAndTraceForm::class
			],
			'singpost.toolbox.redirect_redeliver.index' => [
				'form_label' => 'Redirect Redeliver',
				'form_icon'  => 'redirect-redeliver',
				'form_class' => RedirectRedeliverForm::class
			],
			'singpost.toolbox.find_postal_code.index'   => [
				'form_label' => 'Find Postal Code',
				'form_icon'  => 'find-postal-code'
			],
			'singpost.toolbox.locate_us.index'          => [
				'form_label' => 'Locate Us',
				'form_icon'  => 'locate-us',
				'form_class' => LocateUsForm::class
			],
			'singpost.toolbox.calculate_postage.index'  => [
				'form_label' => 'Calculate Postage',
				'form_icon'  => 'letter-parcel'
			]
		];
	}

	/**
	 * @return array
	 */
	private function _getTools(){
		$tools   = static::getTools();
		$options = [];

		foreach ($tools as $tool_id => $tool){
			$options[$tool_id] = $tool['form_label'];
		}

		return $options;
	}

	/**
	 * @return array
	 */
	public function defaultConfiguration(){
		return [
			'label_display' => FALSE,
			'tool'          => ''
		];
	}

	/**
	 * @param $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 *
	 * @return array|void
	 */
	public function blockForm($form, FormStateInterface $form_state){
		$form['tool'] = [
			'#type'          => 'select',
			'#title'         => $this->t('Tool'),
			'#description'   => $this->t('Select the tool that will be enabled in the frontend.'),
			'#default_value' => $this->configuration['tool'],
			'#options'       => static::_getTools()
		];

		return $form;
	}

	/**
	 * @param $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 */
	public function blockSubmit($form, FormStateInterface $form_state){
		$this->configuration['tool'] = $form_state->getValue('tool');
	}
}
