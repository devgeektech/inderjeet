<?php


namespace Drupal\singpost_sgx_announcements\Controller;


use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Form\FormBuilder;
use Drupal\singpost_sgx_announcements\Form\SgxAnnouncementDeleteForm;
use Drupal\singpost_sgx_announcements\Form\SgxAnnouncementForm;
use Drupal\singpost_sgx_announcements\Form\SgxAnnouncementSearchForm;
use Drupal\singpost_sgx_announcements\Form\SgxAnnouncementTableForm;
use Drupal\singpost_sgx_announcements\Model\SgxAnnouncement;
use Drupal\singpost_sgx_announcements\Repositories\SgxAnnouncementRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class SgxAnnouncementController
 *
 * @package Drupal\singpost_sgx_announcements\Controller
 */
class SgxAnnouncementController extends ControllerBase{

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
	 * SGX Announcement model instance
	 *
	 * @var SgxAnnouncementRepository
	 */
	protected $_service;

	/**
	 * Date formatter service
	 *
	 * @var DateFormatter
	 */
	protected $_date_format;

	/**
	 * The Form builder.
	 *
	 * @param FormBuilder $form_builder
	 * @param DateFormatter $date_format
	 * @param Request $request
	 * @param SgxAnnouncementRepository $sgx_announcement
	 */
	public function __construct(
		FormBuilder $form_builder,
		DateFormatter $date_format,
		Request $request,
		SgxAnnouncementRepository $sgx_announcement){
		$this->_form_builder = $form_builder;
		$this->_date_format  = $date_format;
		$this->_request      = $request;
		$this->_service      = $sgx_announcement;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function create(ContainerInterface $container){
		return new static($container->get('form_builder'),
			$container->get('date.formatter'),
			$container->get('request_stack')->getCurrentRequest(),
			$container->get('singpost.sgx.announcement.service'));
	}

	/**
	 * @return mixed
	 */
	public function index(){
		//Filter
		$search = new SgxAnnouncementSearchForm($this->_request, $this->_service);

		$content['search_form'] = $this->_form_builder->getForm($search);

		//Table
		$table_instance = new SgxAnnouncementTableForm($this->_service, $this->_date_format,
			$search->getFilterQuery());

		$content['table'] = $this->_form_builder->getForm($table_instance);

		//Pagination
		$content['pager'] = [
			'#type' => 'pager',
		];

		return $content;
	}

	/**
	 * @return mixed
	 */
	public function add(){
		$sgx_announcement = new SgxAnnouncement();
		$form             = new SgxAnnouncementForm($this->_service, $sgx_announcement,
			$this->_date_format);

		$build['form'] = $this->_form_builder->getForm($form);

		return $build;
	}

	/**
	 * @param int $id
	 *
	 * @return mixed
	 */
	public function edit(int $id){
		$sgx_announcement = SgxAnnouncement::findOrFail($id);
		$form             = new SgxAnnouncementForm($this->_service, $sgx_announcement,
			$this->_date_format);

		$build['form'] = $this->_form_builder->getForm($form);

		return $build;
	}

	/**
	 * @param int $id
	 *
	 * @return mixed
	 */
	public function delete(int $id){
		$sgx_announcement = SgxAnnouncement::findOrFail($id);
		$form             = new SgxAnnouncementDeleteForm($sgx_announcement);
		$build['form']    = $this->_form_builder->getForm($form);

		return $build;
	}

	/**
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 *
	 * @return \Symfony\Component\HttpFoundation\JsonResponse
	 */
	public function changeStatus(Request $request){
		$id     = $request->get('id');
		$status = $request->get('active', 1);

		$sgx_announcement = SgxAnnouncement::findOrFail($id);

		return new JsonResponse($sgx_announcement->updateAttributes(['published' => $status]));
	}
}