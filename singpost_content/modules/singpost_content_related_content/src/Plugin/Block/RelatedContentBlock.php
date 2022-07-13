<?php

namespace Drupal\singpost_content_related_content\Plugin\Block;

use Drupal;
use Drupal\Core\Form\FormStateInterface;
use Drupal\singpost_content\BaseBlock;

/**
 * Provides a 'Related Content' Block.
 *
 * @Block(
 *   id = "related_content_block",
 *   admin_label = @Translation("Related Content Block"),
 *   category = @Translation("SingPost"),
 * )
 */
class RelatedContentBlock extends BaseBlock{

	/**
	 * @inheritDoc
	 */
	public function build(){
		$node            = $this->_getCurrentNode();
		$related_content = [];

		$this->configuration['label_display'] = 1;

		//Loop through all paragraphs in selected field of current node and find card_container paragraph
		if ($node && $node->hasField($this->configuration['field'])){
			$field      = $node->get($this->configuration['field'])->getValue();
			$storage    = Drupal::entityTypeManager()->getStorage('paragraph');
			$ids        = array_column($field, 'target_id');
			if (!empty($ids) && isFlipable($ids)) {
					$paragraphs = $storage->loadMultiple($ids);
			}
			if(isset($paragraphs)){
				foreach ((array)$paragraphs as $paragraph){
					if ($paragraph->bundle() == 'card_container'){
						$related_content[] = $paragraph;
					}
				}
			}
		}

		return [
			'#theme'     => 'singpost_content_related_content',
			'#paragraph' => $related_content
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