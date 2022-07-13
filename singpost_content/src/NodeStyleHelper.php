<?php

namespace Drupal\singpost_content;

use Drupal;
use Drupal\Core\File\FileSystemInterface;
use Drupal\node\Entity\Node;

/**
 * Class NodeStyleHelper
 *
 * @package Drupal\singpost_content
 */
class NodeStyleHelper{

	/**
	 * @param $stylesheet
	 * @param \Drupal\node\Entity\Node $node
	 */
	public static function saveStyle($stylesheet, Node $node){
		/** @var FileSystemInterface $file_system */
		$file_system = Drupal::service('file_system');
		$directory   = 'public://stylesheet';
		$file_system->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY);
		$file = $directory . '/node-' . $node->id() . '.css';

		$file_system->saveData($stylesheet, $file, FileSystemInterface::EXISTS_REPLACE);
	}

	/**
	 * @param \Drupal\node\Entity\Node $node
	 *
	 * @return string
	 */
	public static function getStyle(Node $node){
		/** @var FileSystemInterface $file_system */
		$file_system = Drupal::service('file_system');
		$file        = 'public://stylesheet';

		return @file_get_contents($file_system->realpath($file) . '/node-' . $node->id() . '.css') ?? '';
	}
}