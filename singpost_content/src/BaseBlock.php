<?php

namespace Drupal\singpost_content;

use Drupal;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Class BaseBlock
 *
 * @package Drupal\singpost_content
 */
class BaseBlock extends BlockBase implements BlockPluginInterface{

	/**
	 * @inheritDoc
	 */
	public function build(){

	}

	/**
	 * Get all fields definitions
	 *
	 * @return array
	 * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
	 * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
	 */
	protected function _getFieldsDefinition(){
		$names = [
			'' => $this->t('- Select -'),
		];

		$entity_storage = Drupal::entityTypeManager()->getStorage('field_config');

		if (!empty($entity_storage) && isFlipable($entity_storage)) {
				$nodes = $entity_storage->loadMultiple();
		}

		foreach ($nodes as $entity){
			$entity_id   = $entity->id();
			$entity_name = $entity->get('field_name');
			$label       = $entity->label();

			if ($label){
				$names[$entity_name] = new TranslatableMarkup('@label (@id)',
					['@label' => $label, '@id' => $entity_id]);
			}else{
				$names[$entity_name] = $entity_id;
			}
		}

		return $names;
	}

	/**
	 * @return \Drupal\Core\Entity\EntityInterface|mixed|null
	 * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
	 * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
	 */
	protected function _getCurrentNode(){
		$node = Drupal::routeMatch()->getParameter('node');

		if (!empty($node) && (is_string($node) || is_numeric($node))){
			return Drupal::entityTypeManager()->getStorage('node')->load($node);
		}

		return $node;
	}
}