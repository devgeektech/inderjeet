<?php

namespace Drupal\singpost_announcements\Entity;

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
 *   id = "singpost_announcement",
 *   label = @Translation("SingPost Announcement"),
 *   base_table = "announcement",
 *   entity_keys = {
 *     "id" = "id",
 *     "title" = "title",
 *     "published" = "published"
 *   },
 *   links = {
 *     "canonical" = "/send-receive/service-announcement/{singpost_announcement}"
 *   }
 * )
 */
class SingpostAnnouncement extends ContentEntityBase{

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

		$fields['summary'] = BaseFieldDefinition::create('string')
		                                        ->setLabel(new TranslatableMarkup('Summary'))
		                                        ->setRequired(FALSE);

		$fields['formatted_content'] = BaseFieldDefinition::create('string')
		                                                  ->setLabel(new TranslatableMarkup('Content'))
		                                                  ->setRequired(FALSE);

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
	public function getContent(){
		return $this->get('formatted_content')->getString();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSummary(){
		return $this->get('summary')->value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCacheTagsToInvalidate(){
		return ['singpost_announcement'];
	}
}
