<?php

namespace Drupal\singpost_announcements\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Form\FormBuilder;
use Drupal\singpost_announcements\Form\AnnouncementDeleteForm;
use Drupal\singpost_announcements\Form\AnnouncementForm;
use Drupal\singpost_announcements\Form\AnnouncementSearchForm;
use Drupal\singpost_announcements\Form\AnnouncementTableForm;
use Drupal\singpost_announcements\Model\Announcement;
use Drupal\singpost_announcements\Repositories\AnnouncementRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AnnouncementController
 *
 * @package Drupal\singpost_announcements\Controller
 */
class AnnouncementController extends ControllerBase{

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
	 * @var AnnouncementRepository
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
	 * @param AnnouncementRepository $announcement
	 */
	public function __construct(
		FormBuilder $form_builder,
		DateFormatter $dateformat,
		Request $request,
		AnnouncementRepository $announcement){
		$this->_form_builder = $form_builder;
		$this->_dateformat   = $dateformat;
		$this->_request      = $request;
		$this->_service      = $announcement;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function create(ContainerInterface $container){
		return new static($container->get('form_builder'),
			$container->get('date.formatter'),
			$container->get('request_stack')->getCurrentRequest(),
			$container->get('singpost.announcement.service'));
	}

	/**
	 * Announcements listing table
	 *
	 * @return mixed
	 */
	public function index(){
		//Filter
		$search = new AnnouncementSearchForm($this->_request, $this->_dateformat,
			$this->_service);

		$content['search_form'] = $this->_form_builder->getForm($search);

		//Table
		$table_instance = new AnnouncementTableForm($this->_service, $this->_dateformat,
			$search->getFilterQuery());

		$content['table'] = $this->_form_builder->getForm($table_instance);

		//Pagination
		$content['pager'] = [
			'#type' => 'pager',
		];

		return $content;
	}

	/**
	 * Announcement add form
	 *
	 * @return mixed
	 */
	public function add(){
		$announcement = new Announcement();
		$form         = new AnnouncementForm($this->_service, $announcement, $this->_dateformat);

		if (!empty($announcement->content) && !is_array($announcement->content)){
			$announcement->content = Json::decode($announcement->content);
		}

		$build['form'] = $this->_form_builder->getForm($form);

		return $build;
	}

	/**
	 * Announcement edit form
	 *
	 * @param int $id
	 *
	 * @return \Drupal\singpost_announcements\Form\AnnouncementForm
	 */
	public function edit(int $id){
		$announcement  = Announcement::findOrFail($id);
		$form          = new AnnouncementForm($this->_service, $announcement, $this->_dateformat);
		$build['form'] = $this->_form_builder->getForm($form);

		return $build;
	}

	/**
	 * Announcement delete form
	 *
	 * @param int $id
	 *
	 * @return mixed
	 */
	public function delete(int $id){
		$announcement  = Announcement::findOrFail($id);
		$form          = new AnnouncementDeleteForm($announcement);
		$build['form'] = $this->_form_builder->getForm($form);

		return $build;
	}

	/**
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 *
	 * @return mixed
	 */
	public function changeStatus(Request $request){
		$id     = $request->get('id');
		$status = $request->get('active', 1);

		$announcement = Announcement::findOrFail($id);

		return new JsonResponse($announcement->updateAttributes(['published' => $status]));
	}
}