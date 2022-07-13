<?php

namespace Drupal\singpost_audit_trail\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Form\FormBuilder;
use Drupal\singpost_audit_trail\Form\AuditTrailForm;
use Drupal\singpost_audit_trail\Form\AuditTrailSearchForm;
use Drupal\singpost_audit_trail\Form\AuditTrailTableForm;
use Drupal\singpost_audit_trail\Model\AuditTrail;
use Drupal\singpost_audit_trail\Repositories\AuditTrailRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AuditTrailController
 *
 * @package Drupal\singpost_audit_trail\Controller
 */
class AuditTrailController extends ControllerBase{

	/**
	 * The Form builder.
	 *
	 * @var FormBuilder
	 */
	protected $_form_builder;

	/**
	 * @var Request
	 */
	protected $_request;

	/**
	 * Announcement model instance
	 *
	 * @var AuditTrailRepository
	 */
	protected $_service;

	/**
	 * Date formatter service
	 *
	 * @var \Drupal\Core\Datetime\DateFormatter
	 */
	protected $_dateformat;

	/**
	 * The Form builder.
	 *
	 * @param FormBuilder $form_builder
	 * @param DateFormatter $dateformat
	 * @param Request $request
	 * @param AuditTrailRepository $audit_trai
	 */
	public function __construct(
		FormBuilder $form_builder,
		DateFormatter $dateformat,
		Request $request,
		AuditTrailRepository $audit_trai){
		$this->_form_builder = $form_builder;
		$this->_dateformat   = $dateformat;
		$this->_request      = $request;
		$this->_service      = $audit_trai;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function create(ContainerInterface $container){
		return new static($container->get('form_builder'),
			$container->get('date.formatter'),
			$container->get('request_stack')->getCurrentRequest(),
			$container->get('singpost.audit_trail.service'));
	}

	/**
	 * @return mixed
	 */
	public function index(){
		//Filter
		$search = new AuditTrailSearchForm($this->_request, $this->_dateformat,
			$this->_service);

		$content['search_form'] = $this->_form_builder->getForm($search);

		//Table
		$table_instance = new AuditTrailTableForm($this->_service, $this->_dateformat,
			$search->getFilterQuery());

		$content['table'] = $this->_form_builder->getForm($table_instance);

		//Pagination
		$content['pager'] = [
			'#type' => 'pager',
		];

		return $content;
	}

	/**
	 * @param int $id
	 *
	 * @return AuditTrailForm
	 */
	public function view(int $id){
		$audit_trail   = AuditTrail::findOrFail($id);
		$form          = new AuditTrailForm($this->_service, $audit_trail, $this->_dateformat);
		$build['form'] = $this->_form_builder->getForm($form);

		return $build;
	}
}