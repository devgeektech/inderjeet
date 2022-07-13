<?php

namespace Drupal\singpost_content_bulk_mail_solutions\Helper;

use Drupal;
use Drupal\Component\Serialization\Json;
use Drupal\singpost_audit_trail\Model\AuditTrail;
use SimpleXMLElement;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class Calculator
 *
 * @package Drupal\singpost_content_bulk_mail_solutions\Helper
 */
class Calculator{

	const MODULE = 'singpost_bulk_mail_solutions';

	public $service_type;

	public $mail_type;

	public $mail_type_sp;

	public $mail_size;

	public $weight;

	public $zone;

	public $volume;

	public $txn_dt;

	public $mail_char;

	public $trans_mode;

	/**
	 * Calculator constructor.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 */
	public function __construct(Request $request){
		$this->service_type = $request->get('service_type');
		$this->mail_type    = $request->get('mail_type');
		$this->mail_type_sp = $request->get('mail_type_sp');
		$this->mail_size    = $request->get('mail_size');
		$this->mail_char    = $request->get('mail_quality');
		$this->weight       = $request->get('weight');
		$this->zone         = $request->get('zone') ?? 'AU';
		$this->volume       = $request->get('volume');
		$this->txn_dt       = date('d-M-Y');
		$this->trans_mode   = 'A';

		if ($this->mail_size == 'SR' && $this->weight >= 50 && $this->weight >= 5000){
			$this->mail_size = 'SL';
		}

		if ($this->service_type == 'O'){
			$this->mail_type = $this->mail_type_sp;
		}

		if ($this->mail_size == 'NS'){
			$this->mail_char = 'NMP';
		}
	}

	/**
	 * @return array|mixed
	 */
	public function calculate(){
		$volume_val = (double) $this->volume;
		$service_val = $this->service_type;
		if($volume_val < 1500 && $service_val == 'L' ){
			$build = [
				'#theme' => 'non_bulk_mails',
				'#data'  => [
					'non_bulk_format'         => Drupal::config('singpost.webform.bulk_mail_solutions')->get('non_bulk_mail') ?? ''
				]
			];
		}else{
			$result = $this->request();

			if (!is_array($result)){
				return $result;
			}

			if ($result['ErrorCode'] != 0){
				return $result['ErrorMessage'];
			}

			$unit_rate       = $result['UnitRate'];
			$unit_rate_early = $result['UnitRateEarly'];
			$kg_rate         = $result['KgRate'];

			if ($this->service_type == 'L'){
				$build = [
					'#theme' => 'table_domestic',
					'#data'  => [
						'volume'          => (double) $this->volume,
						'unit_rate'       => number_format((double) $unit_rate, 3, '.', ','),
						'unit_rate_early' => number_format((double) $unit_rate_early, 3, '.', ','),
						'total_normal'    => number_format((double) $unit_rate * (double) $this->volume,
							2, '.', ','),
						'total_early'     => number_format((double) $unit_rate_early * (double) $this->volume,
							2, '.', ','),
						'tooltip'         => Drupal::config('singpost.webform.bulk_mail_solutions')
												->get('early_posting_tooltip') ?? ''
					]
				];
			}else{
				$build = [
					'#theme' => 'table_international',
					'#data'  => [
						'volume'        => (double) $this->volume,
						'weight'        => (double) $this->weight,
						'unit_rate'     => number_format((double) $unit_rate, 3, '.', ','),
						'kg_rate'       => number_format((double) $kg_rate, 2, '.', ','),
						'total_postage' => number_format(((double) $this->volume * (double) $unit_rate) + (((double) $this->volume * (double) $this->weight) / 1000 * (double) $kg_rate),
							2, '.', ',')
					]
				];
			}
		}

		return $build;
	}

	/**
	 * @return mixed
	 */
	protected function request(){
		/** @var \Drupal\Core\Config\ConfigFactoryInterface $config */
		$config      = Drupal::config('singpost.webform.bulk_mail_solutions');
		$url         = $config->get('service_url');
		$author_name = $config->get('blk_header_name');
		$author_key  = $config->get('blk_header_key');
		$log         = $config->get('blk_log_api');
		$esb_timeout = Drupal::service('settings')->get('esb_timeout', 30);

		if (!empty($author_name) && !empty($author_key)){
			$author = "$author_name: $author_key";
		}

		$query_str = http_build_query([
			'ItemWtgm'    => $this->weight,
			'mailType'    => $this->mail_type,
			'mailSize'    => ($this->mail_size == "") ? "SR" : $this->mail_size,
			'volume'      => $this->volume,
			'MailChar'   => ($this->mail_char == "") ? "BNP" : $this->mail_char,
			'TxnDt'       => $this->txn_dt,
			'SvcLocOvsTy' => $this->service_type,
			'TransMode'   => 'A',
			'zone'        => ($this->zone == "") ? "AU" : $this->zone
		]);

		$request = $url . '?' . $query_str;

		$requested_at = Drupal::time()->getCurrentMicroTime();

		$ch = curl_init($request);

		if (isset($author)){
			curl_setopt($ch, CURLOPT_HTTPHEADER, ["$author"]);
		}

		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, $esb_timeout);

		$xmlstr = curl_exec($ch);

		curl_close($ch);

		$valid_xml = is_valid_xml($xmlstr);

		$responded_at = Drupal::time()->getCurrentMicroTime();

		if (!$valid_xml[0]){
			if ($log){
				Drupal::service('singpost.audit_trail.service')
				      ->log('BulkMailSolution', $request, $xmlstr, $request, $requested_at,
					      $responded_at, AuditTrail::TYPE_FAILED);
			}

			return $valid_xml[1];
		}

		if ($log){
			Drupal::service('singpost.audit_trail.service')
			      ->log('BulkMailSolution', $request, $xmlstr, $request, $requested_at,
				      $responded_at, AuditTrail::TYPE_SUCCESS);
		}

		$xml_response = new SimpleXMLElement($xmlstr);

		return Json::decode(Json::encode($xml_response));
	}
}