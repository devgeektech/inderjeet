<?php


namespace Drupal\singpost_packing_material\Plugin\Block;


use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\singpost_packing_material\Repositories\CategoryRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'PMAtPostOffice' Block.
 *
 * @Block(
 *   id = "pmatpostoffice_block",
 *   admin_label = @Translation("Packing Materials at Post Office"),
 *   category = @Translation("SingPost"),
 * )
 */
class PMAtPostOfficeBlock extends BlockBase implements ContainerFactoryPluginInterface{

	protected $category;

	/**
	 * PMAtPostOfficeBlock constructor.
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
	 * @return array
	 */
	public function build(){
		$categories = $this->category->getAllProduct();

		return [
			'#theme'    => 'singpost_pm_post_office',
			'#data'     => $categories,
			'#attached' => [
				'library' => [
					'singpost_packing_material/packing-material'
				]
			],
			'#cache'    => ['max-age' => 0]
		];
	}

	/**
	 * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
	 * @param array $configuration
	 * @param string $plugin_id
	 * @param mixed $plugin_definition
	 *
	 * @return \Drupal\Core\Plugin\ContainerFactoryPluginInterface|\Drupal\singpost_packing_material\Plugin\Block\PMAtPostOfficeBlock
	 */
	public static function create(
		ContainerInterface $container,
		array $configuration,
		$plugin_id,
		$plugin_definition){
		return new static($configuration, $plugin_id, $plugin_definition,
			$container->get('singpost.pm.category.service'));
	}
}