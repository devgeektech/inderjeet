<?php


namespace Drupal\singpost_base\Support;


use Drupal;
use Drupal\Core\Entity\Element\EntityAutocomplete;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class EntityUrlFieldHelper
 *
 * @package Drupal\singpost_base\Support
 */
class EntityUrlFieldHelper{

	/**
	 * Gets the user-entered string as a URI.
	 *
	 * The following two forms of input are mapped to URIs:
	 * - entity autocomplete ("label (entity id)") strings: to 'entity:' URIs;
	 * - strings without a detectable scheme: to 'internal:' URIs.
	 *
	 * This method is the inverse of ::getUriAsDisplayableString().
	 *
	 * @param string $string
	 *   The user-entered string.
	 *
	 * @return string
	 *   The URI, if a non-empty $uri was passed.
	 *
	 * @see static::getUriAsDisplayableString()
	 */
	public static function getUserEnteredStringAsUri($string){
		// By default, assume the entered string is an URI.
		$uri = $string;

		// Detect entity autocomplete string, map to 'entity:' URI.
		$entity_id = EntityAutocomplete::extractEntityIdFromAutocompleteInput($string);

		if ($entity_id !== NULL){
			$uri = 'entity:node/' . $entity_id;
		}// Detect a schemeless string, map to 'internal:' URI.
		elseif (!empty($string) && parse_url($string, PHP_URL_SCHEME) === NULL){
			if (strpos($string, '<front>') === 0){
				$string = '/' . substr($string, strlen('<front>'));
			}
			$uri = 'internal:' . $string;
		}

		return $uri;
	}

	/**
	 * Gets the URI without the 'internal:' or 'entity:' scheme.
	 *
	 * The following two forms of URIs are transformed:
	 * - 'entity:' URIs: to entity autocomplete ("label (entity id)") strings;
	 * - 'internal:' URIs: the scheme is stripped.
	 *
	 * This method is the inverse of ::getUserEnteredStringAsUri().
	 *
	 * @param string $uri
	 *   The URI to get the displayable string for.
	 *
	 * @return string
	 *
	 * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
	 * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
	 * @see static::getUserEnteredStringAsUri()
	 */
	public static function getUriAsDisplayableString($uri){
		$scheme = parse_url($uri, PHP_URL_SCHEME);

		// By default, the displayable string is the URI.
		$displayable_string = $uri;

		// A different displayable string may be chosen in case of the 'internal:'
		// or 'entity:' built-in schemes.
		if ($scheme === 'internal'){
			$uri_reference = explode(':', $uri, 2)[1];

			$path = parse_url($uri, PHP_URL_PATH);
			if ($path === '/'){
				$uri_reference = '<front>' . substr($uri_reference, 1);
			}

			$displayable_string = $uri_reference;
		}elseif ($scheme === 'entity'){
			list($entity_type, $entity_id) = explode('/', substr($uri, 7), 2);
			// Show the 'entity:' URI as the entity autocomplete would.
			if ($entity_type == 'node' && $entity = Drupal::entityTypeManager()
			                                              ->getStorage($entity_type)
			                                              ->load($entity_id)){
				$displayable_string = EntityAutocomplete::getEntityLabels([$entity]);
			}
		}

		return $displayable_string;
	}

	/**
	 * Form element validation handler for the 'uri' element.
	 *
	 * Disallows saving inaccessible or untrusted URLs.
	 *
	 * @param $element
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 * @param $form
	 */
	public static function validateUriElement($element, FormStateInterface $form_state, $form){
		$uri = static::getUserEnteredStringAsUri($element['#value']);
		$form_state->setValueForElement($element, $uri);

		// If getUserEnteredStringAsUri() mapped the entered value to a 'internal:'
		// URI , ensure the raw value begins with '/', '?' or '#'.
		if (parse_url($uri, PHP_URL_SCHEME) === 'internal' && !in_array($element['#value'][0],
				['/', '?', '#'], TRUE) && substr($element['#value'], 0, 7) !== '<front>'){
			$form_state->setError($element,
				t('Manually entered paths should start with /, ? or #.'));

			return;
		}
	}
}