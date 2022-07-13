<?php

namespace Drupal\singpost_content_service_enquiry\Controller;

use Drupal;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;

/**
 * Class ServiceEnquiryController
 *
 * @package Drupal\singpost_content_service_enquiry\Controller
 */
class ServiceEnquiryController extends ControllerBase{

	/**
	 * @var Request
	 */
	protected $_request;

	/**
	 * @param Request $request
	 */
	public function __construct(Request $request){
		$this->_request = $request;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function create(ContainerInterface $container){
		return new static($container->get('request_stack')->getCurrentRequest());
	}

	/**
	 * @return \Symfony\Component\HttpFoundation\JsonResponse
	 */
	public function index(){
		$service  = $this->_request->get('service_type');
		$category = $this->_request->get('category');

		$categories = [];
		$config     = Drupal::config('singpost.webform.service_enquiry');
		$data       = $config->get('map_data');

		if ($data){
			$data = Yaml::parse($data);

			if ($data[$service][$category]){
				$categories += $data[$service][$category];
				$categories = array_combine($categories, $categories);
			}

			return new JsonResponse(['' => t('- None -')] + $categories);
		}

		return new JsonResponse(['' => t('- None -')]);
	}
}