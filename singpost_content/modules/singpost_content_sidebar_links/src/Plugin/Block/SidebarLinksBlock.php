<?php

namespace Drupal\singpost_content_sidebar_links\Plugin\Block;

use Drupal;
use Drupal\Core\Form\FormStateInterface;
use Drupal\singpost_content\BaseBlock;

/**
 * Provides a 'Sidebar Links' Block.
 *
 * @Block(
 *   id = "sidebar_links_block",
 *   admin_label = @Translation("Sidebar Links Block"),
 *   category = @Translation("SingPost"),
 * )
 */
class SidebarLinksBlock extends BaseBlock{

	/**
	 * @inheritDoc
	 */
	public function build(){
		$node            = $this->_getCurrentNode();
		$links_paragraph = [];

		//Loop through all paragraphs in selected field of current node and find sidebar_links paragraph
		if ($node && $node->hasField($this->configuration['field'])){
			$field      = $node->get($this->configuration['field'])->getValue();
			$storage    = Drupal::entityTypeManager()->getStorage('paragraph');
			$ids        = array_column($field, 'target_id');
if (!empty($ids) && isFlipable($ids)) {		  
			$paragraphs = $storage->loadMultiple($ids);
}
			foreach ($paragraphs as $paragraph){
				if ($paragraph->bundle() == 'sidebar_links'){
					$links_paragraph[] = $paragraph;
				}
			}
		}

		return [
			'#theme'     => 'singpost_content_sidebar_links',
			'#paragraph' => $links_paragraph,
		];
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 *
	 * @return array
	 * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
	 * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
	 */
	public function blockForm($form, FormStateInterface $form_state){
		$form['field'] = [
			'#title'         => $this->t('Paragraph Field'),
			'#type'          => 'select',
			'#required'      => TRUE,
			'#options'       => $this->_getFieldsDefinition(),
			'#default_value' => $this->configuration['field'] ?? []
		];

		return $form;
	}

	/**
	 * {@inheritdoc}
	 */
	public function blockSubmit($form, FormStateInterface $form_state){
		$this->configuration['field'] = $form_state->getValue('field');
	}

	/**
	 * @inheritDoc
	 */
	public function getCacheMaxAge(){
		return 0;
	}
}