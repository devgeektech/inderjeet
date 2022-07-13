<?php

namespace Drupal\singpost_publications\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Defines the announcement entity class.
 *
 * @ContentEntityType(
 *   id = "singpost_publication",
 *   label = @Translation("SingPost Publications"),
 *   base_table = "publications",
 *   entity_keys = {
 *     "id" = "id",
 *     "title" = "title",
 *     "published" = "published"
 *   },
 *   links = {
 *     "canonical" = "/about-us/publication/{singpost_publication}"
 *   }
 * )
 */
class SingpostPublication extends ContentEntityBase{

	use EntityPublishedTrait;

	/**
	 * {@inheritdoc}
	 */
	public static function baseFieldDefinitions(EntityTypeInterface $entity_type){
		$fields = parent::baseFieldDefinitions($entity_type);

		$fields['title'] = BaseFieldDefinition::create('string')
		                                      ->setLabel(new TranslatableMarkup('Title'))
		                                      ->setDescription(new TranslatableMarkup('The title of announcement.'))
		                                      ->setRequired(TRUE);

		$fields['published'] = BaseFieldDefinition::create('integer')
		                                          ->setLabel(new TranslatableMarkup('Published'))
		                                          ->setRequired(TRUE);

		return $fields;
	}

	/**
	 * {@inheritdoc}
	 */
	public function preSave(EntityStorageInterface $storage){
		parent::preSave($storage);

		$this->setTitle($this->getTitle());
	}

	/**
	 * {@inheritdoc}
	 */
	public function postSave(EntityStorageInterface $storage, $update = TRUE){
		parent::postSave($storage, $update);
	}

	/**
	 * {@inheritdoc}
	 */
	public static function postDelete(EntityStorageInterface $storage, array $entities){
		parent::postDelete($storage, $entities);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTitle(){
		return $this->get('title')->value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setTitle($title){
		$this->set('title', $title);

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function label(){
		return $this->getTitle();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCacheTagsToInvalidate(){
		return ['singpost_publications'];
	}
}
