<?php


namespace Drupal\singpost_base\Support;


use Drupal;
use Drupal\encrypt\Entity\EncryptionProfile;

/**
 * Class Security
 *
 * @package Drupal\singpost_base\Support
 */
class Security{

	/**
	 * @param $instance_id
	 * @param $string
	 *
	 * @return mixed
	 */
	public static function encrypt($instance_id, $string){
		$encryption_profile = EncryptionProfile::load($instance_id);

		$data = [
			'data'       => Drupal::service('encryption')->encrypt($string, $encryption_profile),
			'encrypt_id' => $instance_id
		];

		return serialize($data);
	}

	/**
	 * @param $data_encrypt
	 *
	 * @return mixed
	 */
	public static function decrypt($data_encrypt){
		$unserialized = unserialize($data_encrypt);

		if (isset($unserialized['data']) && isset($unserialized['encrypt_id'])){
			$encryption_profile = EncryptionProfile::load($unserialized['encrypt_id']);

			return Drupal::service('encryption')
			             ->decrypt($unserialized['data'], $encryption_profile);
		}

		return $data_encrypt;
	}
}