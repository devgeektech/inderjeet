<?php

namespace Drupal\singpost_sgx_announcements\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Defines the SGX Announcement entity class.
 *
 * @ContentEntityType(
 *   id = "sgx_announcement",
 *   label = @Translation("SingPost SGX Announcement"),
 *   base_table = "sgx_announcement",
 *   entity_keys = {
 *     "id" = "id",
 *     "title" = "title",
 *     "published" = "published"
 *   },
 *   links = {
 *     "canonical" = "/about-us/investor-relations/sgx-announcements/{sgx_announcement}"
 *   }
 * )
 */
class SgxAnnouncement extends ContentEntityBase{

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
		return ['singpost_sgx_announcements'];
	}
}
