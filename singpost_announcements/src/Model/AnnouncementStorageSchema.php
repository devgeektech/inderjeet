<?php

namespace Drupal\singpost_announcements\Model;

use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorageSchema;

/**
 * Defines the singpost_announcement schema handler.
 */
class AnnouncementStorageSchema extends SqlContentEntityStorageSchema{

	/**
	 * {@inheritdoc}
	 */
	protected function getEntitySchema(ContentEntityTypeInterface $entity_type, $reset = FALSE){
		$schema = parent::getEntitySchema($entity_type, $reset);

		$schema[$this->storage->getBaseTable()]['indexes'] += [
			'announcement_id_title' => ['title', 'id'],
		];

		return $schema;
	}

}
