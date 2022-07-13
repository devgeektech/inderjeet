<?php

namespace Drupal\singpost_toolbox_redirect_redeliver\Helper;

use Drupal\singpost_toolbox\Helper\ToolboxBase;

/**
 * Class RedirectRedeliver
 *
 * @package Drupal\singpost_toolbox_redirect_redeliver\Helper
 */
class RedirectRedeliver extends ToolboxBase{

	public static $config_name = 'redirect_redeliver.config';

	/**
	 * @param $item_no
	 * @param $po_code
	 * @param $po_transfer_code
	 * @param $contact_no
	 * @param $email
	 *
	 * @return array|mixed|\SimpleXMLElement|string
	 */
	public function getRedirect($item_no, $po_code, $po_transfer_code, $contact_no, $email){
		$esb_url = $this->_config->get('redirect_url');

		$root_elem = 'AddSFCaseForRedirectionRequest';

		$children = [
			'ItemNumber'     => $item_no,
			'BeatNumber'     => '',
			'POCode'         => $po_code,
			'POTransferCode' => $po_transfer_code,
			'ContactNo'      => $contact_no,
			'Email'          => $email,
		];

		$input = $this->_generateXmlInput($root_elem, $children);

		$result = $this->_getEsbResult($esb_url, 'xml', $input);

		if (!empty($result) && !isset($result['Status']['ErrorCode']) && $result['Status']['ErrorCode'] == '0'){
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * @param $item_no
	 * @param $po_code
	 * @param $date
	 * @param $contact_no
	 * @param $email
	 *
	 * @return array|mixed|\SimpleXMLElement|string
	 */
	public function getRedeliver($item_no, $po_code, $date, $contact_no, $email){
		$esb_url = $this->_config->get('redeliver_url');

		$root_elem = 'AddSFCaseForRedeliveryResponse';

		$children = [
			'ItemNumber'   => $item_no,
			'BeatNumber'   => '',
			'POCode'       => $po_code,
			'PreferedDate' => $date,
			'ContactNo'    => $contact_no,
			'Email'        => $email,
		];

		$input = $this->_generateXmlInput($root_elem, $children);

		$result = $this->_getEsbResult($esb_url, 'xml', $input);

		if (!empty($result) && !isset($result['Status']['ErrorCode']) && $result['Status']['ErrorCode'] == '0'){
			return TRUE;
		}

		return FALSE;
	}
}