<?php


namespace Drupal\singpost_toolbox\Helper;


/**
 * Name:  Google Invisible reCAPTCHA
 *
 * Author: Geordy James
 *
 * @geordyjames
 *
 * Location: https://github.com/geordyjames/google-Invisible-reCAPTCHA
 * Created:  13.03.2017
 * Created by Geordy James to make a easy version of google Invisible reCAPTCHA PHP Library
 *
 * Description:  This is an unofficial version of google Invisible reCAPTCHA PHP Library
 *
 */
class Recaptcha{

	protected $config;

	/**
	 * Recaptcha constructor.
	 *
	 * @param string $client_key
	 * @param string $secret_key
	 */
	public function __construct($client_key = '', $secret_key = ''){
		$this->config = [
			'client-key' => $client_key,
			'secret-key' => $secret_key
		];
	}

	/**
	 * @param $recaptcha
	 *
	 * @return array
	 */
	public function verifyResponse($recaptcha){

		$remoteIp = $this->getIPAddress();

		// Discard empty solution submissions
		if (empty($recaptcha)){
			return [
				'success'     => FALSE,
				'error-codes' => 'missing-input',
			];
		}

		$getResponse = $this->getHTTP(
			[
				'secret'   => $this->config['secret-key'],
				'remoteip' => $remoteIp,
				'response' => $recaptcha,
			]
		);

		// get reCAPTCHA server response
		$responses = json_decode($getResponse, TRUE);

		if (isset($responses['success']) and $responses['success'] == TRUE){
			$status = TRUE;
		}else{
			$status = FALSE;
			$error  = (isset($responses['error-codes'])) ? $responses['error-codes']
				: 'invalid-input-response';
		}

		return [
			'success'     => $status,
			'error-codes' => (isset($error)) ? $error : NULL,
		];
	}

	/**
	 * @return mixed
	 */
	private function getIPAddress(){
		if (!empty($_SERVER['HTTP_CLIENT_IP'])){
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		}elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}else{
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		return $ip;
	}

	/**
	 * @param $data
	 *
	 * @return false|string
	 */
	private function getHTTP($data){
		$url      = 'https://www.google.com/recaptcha/api/siteverify?' . http_build_query($data);
		$response = file_get_contents($url);

		return $response;
	}
}