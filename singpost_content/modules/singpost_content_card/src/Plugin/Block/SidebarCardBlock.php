<?php


namespace Drupal\singpost_content_card\Plugin\Block;


use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\singpost_content\BaseBlock;

/**
 * Provides a 'Sidebar Card' Block.
 *
 * @Block(
 *   id = "sidebar_card",
 *   admin_label = @Translation("Sidebar Card"),
 *   category = @Translation("SingPost"),
 * )
 */
class SidebarCardBlock extends BaseBlock{

	/**
	 * @return array
	 */
	public function build(){
		$config = $this->configuration;
		$build  = [
			'#theme'  => 'singpost_sidebar_card',
			'#config' => [
				'description' => $config['description'],
				'btn_url'     => $config['btn_url'],
				'btn_label'   => $config['btn_label'],
			]
		];

		if ($config['image']){
			$fid                       = File::load($config['image']);
			$build['#config']['image'] = $fid->createFileUrl();
		}

		$build['#config']['title'] = $this->label();

		return $build;
	}

	/**
	 * {@inheritdoc}
	 */
	public function blockForm($form, FormStateInterface $form_state){
		$form['sidebar_card']['description'] = [
			'#type'          => 'textarea',
			'#title'         => $this->t('Description'),
			'#default_value' => $this->configuration['description'] ?: ''
		];

		$form['sidebar_card']['image'] = [
			'#type'              => 'managed_file',
			'#title'             => $this->t('Image'),
			'#upload_validators' => [
				'file_validate_extensions' => ['gif png jpeg jpg'],
			],
			'#upload_location'   => 'public://upload/sidebar-card/',
			'#description'       => t('Allowed types: @types', ['@types' => 'gif png jpeg jpg']),
			'#default_value'     => $this->configuration['image'] ? [$this->configuration['image']] : ''
		];

		$form['sidebar_card']['btn_url'] = [
			'#type'          => 'textfield',
			'#title'         => $this->t('URL'),
			'#default_value' => $this->configuration['btn_url'] ?: '',
		];

		$form['sidebar_card']['btn_label'] = [
			'#type'          => 'textfield',
			'#title'         => $this->t('URL Label'),
			'#default_value' => $this->configuration['btn_label'] ?: ''
		];

		return $form;
	}

	/**
	 * {@inheritdoc}
	 */
	public function blockSubmit($form, FormStateInterface $form_state){
		$sidebar_card = $form_state->getValue('sidebar_card');
		$image        = $sidebar_card['image'];

		if ($image){
			$file = File::load($image[0]);
			$file->setPermanent();
			$file->save();
			$image = $image[0];
		}else{
			$image = NULL;
		}

		$this->configuration['description'] = $sidebar_card['description'];
		$this->configuration['image']       = $image;
		$this->configuration['btn_url']     = $sidebar_card['btn_url'];
		$this->configuration['btn_label']   = $sidebar_card['btn_label'];
	}

	/**
	 * @return int
	 */
	public function getCacheMaxAge(){
		return 0;
	}
}