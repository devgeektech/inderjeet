<?php

namespace Drupal\singpost_toolbox_locate_us\Plugin\Block;

use Drupal;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\singpost_toolbox_locate_us\Form\Config\LocateUsConfigForm;
use Drupal\singpost_toolbox_locate_us\Frontend\Form\LocateUsForm;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Locate Us Results' Block.
 *
 * @Block(
 *   id = "locate_us_block",
 *   admin_label = @Translation("Locate Us Results"),
 *   category = @Translation("Locate Us Results"),
 * )
 */
class LocateUsResultBlock extends BlockBase implements ContainerFactoryPluginInterface{

	/**
	 * @return array
	 */
	public function build(){
		$form = new LocateUsForm();
		$data = $form->getResults();

		$config    = Drupal::config(LocateUsConfigForm::$config_name);
		$error_msg = $config->get('locate_us_error_message');

		$build = [
			'#theme'     => 'singpost_locate_us_result',
			'#cache'     => ['max-age' => 0],
			'#data'      => $data['results'] ?? [],
			'#icon'      => $data['icon'] ?? NULL,
			'#icon_text' => $data['icon_text'] ?? NULL,
			'#keyword'   => $data['keyword'] ?? NULL,
			'#error_msg' => $error_msg
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
	public function defaultConfiguration(){
		return [
			'label_display' => TRUE,
		];
	}
}