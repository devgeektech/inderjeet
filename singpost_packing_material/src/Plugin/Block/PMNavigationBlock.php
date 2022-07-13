<?php


namespace Drupal\singpost_packing_material\Plugin\Block;


use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\singpost_packing_material\Repositories\CategoryRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Packing Materials Navigation' Block.
 *
 * @Block(
 *   id = "pm_navigation_block",
 *   admin_label = @Translation("Packing Materials Navigation"),
 *   category = @Translation("SingPost"),
 * )
 */
class PMNavigationBlock extends BlockBase implements ContainerFactoryPluginInterface{

	/**
	 * @var \Drupal\singpost_packing_material\Repositories\CategoryRepository
	 */
	protected $category;

	/**
	 * PMNavigationBlock constructor.
	 *
	 * @param array $configuration
	 * @param $plugin_id
	 * @param $plugin_definition
	 * @param \Drupal\singpost_packing_material\Repositories\CategoryRepository $repository
	 */
	public function __construct(
		array $configuration,
		$plugin_id,
		$plugin_definition,
		CategoryRepository $repository){
		parent::__construct($configuration, $plugin_id, $plugin_definition);
		$this->category = $repository;
	}

	/**
	 * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
	 * @param array $configuration
	 * @param string $plugin_id
	 * @param mixed $plugin_definition
	 *
	 * @return \Drupal\Core\Plugin\ContainerFactoryPluginInterface|\Drupal\singpost_packing_material\Plugin\Block\PMNavigationBlock
	 */
	public static function create(
		ContainerInterface $container,
		array $configuration,
		$plugin_id,
		$plugin_definition){
		return new static($configuration, $plugin_id, $plugin_definition,
			$container->get('singpost.pm.category.service'));
	}

	/**
	 * @return array
	 */
	public function build(){
		$config     = $this->configuration;
		$categories = $this->category->getCategories();

		return [
			'#theme'    => 'singpost_pm_navigation',
			'#menus'    => $categories,
			'#config'   => $config,
			'#attached' => [
				'library' => [
					'singpost_packing_material/packing-material'
				]
			],
			'#cache'    => ['max-age' => 0]
		];
	}

	/**
	 * @param $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 *
	 * @return array
	 */
	public function blockForm($form, FormStateInterface $form_state){
		$form['nav_desc']['pm_nav_above'] = [
			'#type'          => 'text_format',
			'#title'         => $this->t('Text Above Nav'),
			'#default_value' => $this->configuration['pm_nav_above']['value'] ?? '',
			'#format'        => $this->configuration['pm_nav_above']['format'] ?? 'basic_html'
		];

		$form['nav_desc']['pm_nav_under'] = [
			'#type'          => 'text_format',
			'#title'         => $this->t('Text Under Nav'),
			'#default_value' => $this->configuration['pm_nav_under']['value'] ?? '',
			'#format'        => $this->configuration['pm_nav_under']['format'] ?? 'basic_html'
		];

		$form['nav_desc']['pm_nav_cart_summary'] = [
			'#type'          => 'text_format',
			'#title'         => $this->t('Text Under Cart Summary'),
			'#default_value' => $this->configuration['pm_nav_cart_summary']['value'] ?? '',
			'#format'        => $this->configuration['pm_nav_cart_summary']['format'] ?? 'basic_html'
		];

		$form['nav_desc']['item_counter'] = array(
		  '#type' => 'number',
		  '#step' => '1',
		  '#default_value' => $this->configuration['item_counter']['value'] ?? '15',
		  '#title' => $this->t('Load More Items Counter'),
		);
		$form['nav_desc']['default_counter'] = array(
		  '#type' => 'number',
		  '#step' => '1',
		  '#default_value' => $this->configuration['default_counter']['value'] ?? '15',
		  '#title' => $this->t('Load More Default Items '),
		);

		return $form;
	}

	/**
	 * @param $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 */
	public function blockSubmit($form, FormStateInterface $form_state){
		$pm_nav = $form_state->getValue('nav_desc');

		$this->configuration['pm_nav_above'] 		= $pm_nav['pm_nav_above'];
		$this->configuration['pm_nav_under'] 		= $pm_nav['pm_nav_under'];
		$this->configuration['pm_nav_cart_summary'] = $pm_nav['pm_nav_cart_summary'];
		$this->configuration['item_counter'] 		= $pm_nav['item_counter'];
		$this->configuration['default_counter'] 	= $pm_nav['default_counter'];
	}
}