<?php


namespace Drupal\singpost_publications\Controller;


use Drupal\Component\Serialization\Json;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBuilder;
use Drupal\singpost_publications\Form\PublicationDeleteForm;
use Drupal\singpost_publications\Form\PublicationForm;
use Drupal\singpost_publications\Form\PublicationSearchForm;
use Drupal\singpost_publications\Form\PublicationTableForm;
use Drupal\singpost_publications\Model\Publication;
use Drupal\singpost_publications\Repositories\PublicationRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class PublicationController
 *
 * @package Drupal\singpost_publications\Controller
 */
class PublicationController extends ControllerBase{

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
	 * @var PublicationRepository
	 */
	protected $_service;

	/**
	 * PublicationController constructor.
	 *
	 * @param \Drupal\Core\Form\FormBuilder $form_builder
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @param \Drupal\singpost_publications\Repositories\PublicationRepository $publication
	 */
	public function __construct(
		FormBuilder $form_builder,
		Request $request,
		PublicationRepository $publication){
		$this->_form_builder = $form_builder;
		$this->_request      = $request;
		$this->_service      = $publication;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function create(ContainerInterface $container){
		return new static($container->get('form_builder'),
			$container->get('request_stack')->getCurrentRequest(),
			$container->get('singpost.publication.service'));
	}

	/**
	 * @return mixed
	 */
	public function index(){
		//Filter
		$search = new PublicationSearchForm($this->_request, $this->_service);

		$content['search_form'] = $this->_form_builder->getForm($search);

		//Table
		$table_instance = new PublicationTableForm($this->_service, $search->getFilterQuery());

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
		$publication = new Publication();
		$form        = new PublicationForm($this->_service, $publication);

		if (!empty($publication->content) && !is_array($publication->content)){
			$publication->content = Json::decode($publication->content);
		}

		$build['form'] = $this->_form_builder->getForm($form);

		return $build;
	}

	/**
	 * @param int $id
	 *
	 * @return mixed
	 */
	public function edit(int $id){
		$publication   = Publication::findOrFail($id);
		$form          = new PublicationForm($this->_service, $publication);
		$build['form'] = $this->_form_builder->getForm($form);

		return $build;
	}

	/**
	 * @param int $id
	 *
	 * @return mixed
	 */
	public function delete(int $id){
		$publication   = Publication::findOrFail($id);
		$form          = new PublicationDeleteForm($publication);
		$build['form'] = $this->_form_builder->getForm($form);

		return $build;
	}

	/**
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 *
	 * @return \Symfony\Component\HttpFoundation\JsonResponse
	 */
	public function changeStatus(Request $request){
		$id          = $request->get('id');
		$status      = $request->get('active', 1);
		$publication = Publication::findOrFail($id);

		return new JsonResponse($publication->updateAttributes(['published' => $status]));
	}
}