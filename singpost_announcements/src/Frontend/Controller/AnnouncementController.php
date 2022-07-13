<?php


namespace Drupal\singpost_announcements\Frontend\Controller;


use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBuilder;
use Drupal\Core\Url;
use Drupal\singpost_announcements\Model\Announcement;
use Drupal\singpost_announcements\Frontend\Form\AnnouncementFilterForm;
use Drupal\singpost_announcements\Frontend\Form\AnnouncementListForm;
use Drupal\singpost_announcements\Repositories\AnnouncementRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AnnouncementController
 *
 * @package Drupal\singpost_announcements\Frontend\Controller
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
	 * AnnouncementController constructor.
	 *
	 * @param \Drupal\Core\Form\FormBuilder $form_builder
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @param \Drupal\singpost_announcements\Repositories\AnnouncementRepository $repository
	 */
	public function __construct(
		FormBuilder $form_builder,
		Request $request,
		AnnouncementRepository $repository){
		$this->_form_builder = $form_builder;
		$this->_request      = $request;
		$this->_service      = $repository;
	}

	/**
	 * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
	 *
	 * @return \Drupal\Core\Controller\ControllerBase|\Drupal\singpost_announcements\Frontend\Controller\AnnouncementController
	 */
	public static function create(ContainerInterface $container){
		return new static($container->get('form_builder'),
			$container->get('request_stack')->getCurrentRequest(),
			$container->get('singpost.announcement.service'));
	}

	/**
	 * @return mixed
	 */
	public function index(){
		// die("here");
		$filter_form       = new AnnouncementFilterForm($this->_request, $this->_service);
		$content['filter'] = $this->_form_builder->getForm($filter_form);

		$table_instance   = new AnnouncementListForm($this->_service,
			$filter_form->getFilterQuery());
		$content['table'] = $this->_form_builder->getForm($table_instance);

		return $content;
	}

	/**
	 * Announcement view page -> Redirect to index page
	 *
	 * @param int $singpost_announcement
	 *
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 */
	public function view(int $singpost_announcement){
		return new RedirectResponse(Url::fromRoute('singpost.announcements.index')->toString());
	}
	
		/**
	 * @return mixed
	 */
	public function single_announcement(int $singpost_announcement){
		$announcement = new Announcement();
		$result = $announcement->singleAnnoucement($singpost_announcement);
		 return [
			  '#theme' => 'singpost_announcement_single',
			  '#results' => $result,
			];
	}
}