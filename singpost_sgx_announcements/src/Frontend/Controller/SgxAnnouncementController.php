<?php


namespace Drupal\singpost_sgx_announcements\Frontend\Controller;


use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBuilder;
use Drupal\Core\Url;
use Drupal\singpost_sgx_announcements\Frontend\Form\SgxAnnouncementFilterForm;
use Drupal\singpost_sgx_announcements\Frontend\Form\SgxAnnouncementListForm;
use Drupal\singpost_sgx_announcements\Repositories\SgxAnnouncementRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class SgxAnnouncementController
 *
 * @package Drupal\singpost_sgx_announcements\Frontend\Controller
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
	 * SgxAnnouncementController constructor.
	 *
	 * @param \Drupal\Core\Form\FormBuilder $form_builder
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @param \Drupal\singpost_sgx_announcements\Repositories\SgxAnnouncementRepository $sgx_announcement
	 */
	public function __construct(
		FormBuilder $form_builder,
		Request $request,
		SgxAnnouncementRepository $sgx_announcement){
		$this->_form_builder = $form_builder;
		$this->_request      = $request;
		$this->_service      = $sgx_announcement;
	}

	/**
	 * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
	 *
	 * @return \Drupal\Core\Controller\ControllerBase|\Drupal\singpost_sgx_announcements\Frontend\Controller\SgxAnnouncementController
	 */
	public static function create(ContainerInterface $container){
		return new static($container->get('form_builder'),
			$container->get('request_stack')->getCurrentRequest(),
			$container->get('singpost.sgx.announcement.service'));
	}

	/**
	 * @return mixed
	 */
	public function index(){
		//Filter
		$search = new SgxAnnouncementFilterForm($this->_request, $this->_service);

		$content['search_form'] = $this->_form_builder->getForm($search);

		//Table
		$table_instance = new SgxAnnouncementListForm($this->_service, $search->getFilterQuery());

		$content['table'] = $this->_form_builder->getForm($table_instance);

		return $content;
	}

	/**
	 * Publication view page -> Redirect to index page
	 *
	 * @param int $sgx_announcement
	 *
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 */
	public function view(int $sgx_announcement){
		return new RedirectResponse(Url::fromRoute('singpost.sgx.announcements.index')->toString());
	}
}