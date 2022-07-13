<?php

namespace Drupal\singpost_base\Plugin\Block;


use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Custom HTML' Block.
 *
 * @Block(
 *   id = "custom_html_block",
 *   admin_label = @Translation("Custom HTML Block"),
 *   category = @Translation("Custom HTML"),
 * )
 */
class CustomBlock extends BlockBase implements ContainerFactoryPluginInterface{

	/**
	 * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
	 * @param array $configuration
	 * @param string $plugin_id
	 * @param mixed $plugin_definition
	 *
	 * @return \Drupal\singpost_base\Plugin\Block\CustomBlock|static
	 */
	public static function create(
		ContainerInterface $container,
		array $configuration,
		$plugin_id,
		$plugin_definition){
		return new static($configuration, $plugin_id, $plugin_definition);
	}

	/**
	 * @inheritDoc
	 */
	public function build(){
		$title = $this->configuration['label_display'] ? $this->configuration['label'] : '';

		return [
			'#theme'             => 'custom_html_block',
			'#html'              => $this->configuration['html']['value'],
			'#block_title'       => $title,
			'#block_class'       => $this->configuration['block_class'],
			'#block_inner_class' => $this->configuration['block_inner_class']
		];
	}

	/**
	 * @inheritDoc
	 */
	public function defaultConfiguration(){
		return [
			'label_display'     => FALSE,
			'html'              => '',
			'block_class'       => '',
			'block_inner_class' => ''
		];
	}

	/**
	 * @inheritDoc
	 */
	public function blockForm($form, FormStateInterface $form_state){
		$form['custom_block']['block_class'] = [
			'#type'          => 'textfield',
			'#title'         => 'Block class name',
			'#default_value' => $this->configuration['block_class'] ?: NULL
		];

		$form['custom_block']['block_inner_class'] = [
			'#type'          => 'textfield',
			'#title'         => 'Block inner class name',
			'#default_value' => $this->configuration['block_inner_class'] ?: NULL
		];

		$form['custom_block']['html'] = [
			'#type'          => 'text_format',
			'#title'         => $this->t('HTML'),
			'#default_value' => $this->configuration['html'] ? $this->configuration['html']['value'] : NULL,
			'#format'        => $this->configuration['html'] ? $this->configuration['html']['format'] : NULL
		];

		return $form;
	}

	/**
	 * {@inheritdoc}
	 */
	public function blockSubmit($form, FormStateInterface $form_state){
		$block_branding                           = $form_state->getValue('custom_block');
		$this->configuration['html']              = $block_branding['html'];
		$this->configuration['block_class']       = $block_branding['block_class'];
		$this->configuration['block_inner_class'] = $block_branding['block_inner_class'];
	}

}