<?php


namespace Drupal\singpost_toolbox_track_and_trace\Plugin\Block;


use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\singpost_toolbox_track_and_trace\Frontend\Form\TrackAndTraceForm;
use Drupal\singpost_toolbox_track_and_trace\Helper\TrackAndTrace;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @Block(
 *   id = "track_and_trace_result_block",
 *   admin_label = @Translation("Track and Trace Result Block"),
 *   category = @Translation("SingPost Toolbox")
 * )
 */
class TrackAndTraceResult extends BlockBase implements ContainerFactoryPluginInterface{

	/**
	 * @return array
	 */
	public function build(){
		$helper  = new TrackAndTrace();
		$results = $this->_getResult();
		$data    = [];
		if (!empty($results['data']) && is_array($results['data']) && empty($results['track_formt'])){
			foreach ($results['data'] as $result){
				if (!empty($result['ErrorCode'])){
					if ($result['ErrorCode'] == '1001'){
						$data['ErrorDesc'] = $helper->getErrorTrackingMessage();
					}else{
						$data['ErrorDesc'] = $result['ErrorDesc'];
					}
				}else{
					if (empty($result['TrackingNumberFound']) || $result['TrackingNumberFound'] == 'false'){
						$result['NotFound'] = t($helper->getNotFoundTrackingMessage());
						//$result['NotFound'] = 'Item status not found in the system.';
						//$result['NotFound'] = 'Item status not found in the system.';
						unset($result['DeliveryStatusDetails']);
					}else{
						if ($result['ItemType'] && $result['ItemType'] != '-'){
							$info = [];

							if ($result['ItemType'] == 'Speedpost' || $result['ItemType'] == 'VPost'){
								if ($result['ServiceType'] && $result['ServiceType'] != '-'){
									$info['ServiceType'] = [
										'name'  => 'Service Type',
										'value' => $result['ServiceType']
									];
								}
								if ($result['OriginalCountry'] && $result['OriginalCountry'] != '-'){
									$info['OriginalCountry'] = [
										'name'  => 'Origin Country',
										'value' => $result['OriginalCountry']
									];
								}
								if ($result['OriginPostalCode'] && $result['OriginPostalCode'] != '-'){
									$info['OriginPostalCode'] = [
										'name'  => 'Origin Exchange/Post Office',
										'value' => $result['OriginPostalCode']
									];
								}
								if ($result['OtherReferenceNo'] && $result['OtherReferenceNo'] != '-'){
									$info['OtherReferenceNo'] = [
										'name'  => 'Other Reference No',
										'value' => $result['OtherReferenceNo']
									];
								}
								if ($result['DestinationCountry'] && $result['DestinationCountry'] != '-'){
									$info['DestinationCountry'] = [
										'name'  => 'Destination Country',
										'value' => $result['DestinationCountry']
									];
								}
								if ($result['RecipientName'] && $result['RecipientName'] != '-'){
									$info['RecipientName'] = [
										'name'  => 'RECIPIENT\'S NAME',
										'value' => $result['RecipientName']
									];
								}
							}else{
								if ($result['DispatchNo'] && $result['DispatchNo'] != '-'){
									$info['DispatchNo'] = [
										'name'  => 'Dispatch No',
										'value' => $result['DispatchNo']
									];
								}
								if ($result['LatestBeatNo'] && $result['LatestBeatNo'] != '-'){
									$info['LatestBeatNo'] = [
										'name'  => 'Beat No',
										'value' => $result['LatestBeatNo']
									];
								}
							}

							$result['InfoItemType'] = $info;

							$type = $helper->getAlterItemType($result['ItemType'],
								$result['TrackingNumber']);

							if (in_array($type, $helper::$item_type)){
								$result['ShowLocation'] = FALSE;
							}else{
								$result['ShowLocation'] = TRUE;
							}
						}

						if ($result['AlternativeTrackingNumber'] && !empty($result['AlternativeTrackingNumber'])){
							$result['AlternativeTrackingNumber'] = $helper->getAlterTrackingNumber($result['AlternativeTrackingNumber']);
						}

						if (!empty($result['DeliveryStatusDetails']['DeliveryStatusDetail'])){
							$status_details = $result['DeliveryStatusDetails']['DeliveryStatusDetail'];

							if (!empty($status_details['Date'])){
								$status_details = [$status_details];
							}

							usort($status_details, function ($first, $second){
								return strtotime($second['Date']) - strtotime($first['Date']);
							});

							if (!empty($status_details[0]['StatusDescription'])){
								$result['StatusDescription'] = $status_details[0]['StatusDescription'];
							}

							if (!empty($status_details[0]['Date'])){
								$result['StatusDate'] = $status_details[0]['Date'];
							}

							foreach ($status_details as $key => $status){
								$status_description = $status['StatusDescription'] ?? '';
								$ace_status_code    = $status['AceStatusCode'] ?? '';
								$tnt_type_id        = $status['TrackAndTraceTypeId'] ?? '';
								$event_type_id      = $status['EventTransactionId'] ?? '';
								$service_type       = $result['ServiceType'] ?? '';
								$item_type          = $result['ItemType'] ?? '';

								$status_details[$key]['DestinationCountry'] = $helper->getDestinationCountry($status_description,
									$ace_status_code, $tnt_type_id, $event_type_id);

								if (($tnt_type_id != $helper::TRACK_AND_TRACE_TYPE_ID && $event_type_id != $helper::EVENT_TRANSACTION_ID && !str_contains(strtoupper($status_description),
											'POP')) && ($service_type == '' || strtolower($item_type) == 'mail' || preg_match("#^R[A-Z](.*)$#i",
											$result['TrackingNumber']))){
									$status_details[$key]['RedeliverTooltip'] = $helper->getRedeliverStatus($status_description);
								}
							}

							$result['DeliveryStatus'] = $status_details;
						}
					}

					$data[$result['TrackingNumber']] = $result;
				}
			}
		}
		else{
			$result['NotFound'] = $results['track_formt'];
		}

		return [
			'#theme'    => 'singpost_track_and_trace_result',
			'#cache'    => ['max-age' => 0],
			'#attached' => [
				'library' => [
					'singpost_toolbox_track_and_trace/form'
				]
			],
			'#results'  => $data,
			'#help'     => [
				'tracking_nos' => $results['tracking_nos'],
				'other_note'   => $helper->getOtherNotes(),
				'error'        => $helper->getErrorMessage(),
				'error_status' => $result['NotFound']
			]
		];
	}

	/**
	 * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
	 * @param array $configuration
	 * @param string $plugin_id
	 * @param mixed $plugin_definition
	 *
	 * @return \Drupal\Core\Plugin\ContainerFactoryPluginInterface|\Drupal\singpost_toolbox_track_and_trace\Plugin\Block\TrackAndTraceResult
	 */
	public static function create(
		ContainerInterface $container,
		array $configuration,
		$plugin_id,
		$plugin_definition){
		return new static($configuration, $plugin_id, $plugin_definition);
	}

	/**
	 * @return array|int|mixed|\SimpleXMLElement|string
	 */
	private function _getResult(){
		$tnt_form = new TrackAndTraceForm();
		return $tnt_form->getResults();
	}
}