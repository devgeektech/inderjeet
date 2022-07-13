<?php


namespace Drupal\singpost_toolbox_locate_us\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBuilder;
use Drupal\singpost_toolbox_locate_us\Form\LocateUsTypeDeleteForm;
use Drupal\singpost_toolbox_locate_us\Form\LocateUsTypeForm;
use Drupal\singpost_toolbox_locate_us\Form\LocateUsTypeSearchForm;
use Drupal\singpost_toolbox_locate_us\Form\LocateUsTypeTableForm;
use Drupal\singpost_toolbox_locate_us\Model\LocateUsType;
use Drupal\singpost_toolbox_locate_us\Repositories\LocateUsRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class LocateUsController
 *
 * @package Drupal\singpost_toolbox_locate_us\Controller
 */
class LocateUsController extends ControllerBase{

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
	 * LocateUsType model instance
	 *
	 * @var LocateUsRepository
	 */
	protected $_service;


	/**
	 * The Form builder.
	 *
	 * @param FormBuilder $form_builder
	 * @param Request $request
	 * @param LocateUsRepository $locate_us_type
	 */
	public function __construct(
		FormBuilder $form_builder,
		Request $request,
		LocateUsRepository $locate_us_type){
		$this->_form_builder = $form_builder;
		$this->_request      = $request;
		$this->_service      = $locate_us_type;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function create(ContainerInterface $container){
		return new static($container->get('form_builder'),
			$container->get('request_stack')->getCurrentRequest(),
			$container->get('singpost.toolbox.locate_us.service'));
	}

	/**
	 * LocateUsTypes listing table
	 *
	 * @return mixed
	 */
	public function index(){

		//Filter
		$search = new LocateUsTypeSearchForm($this->_request, $this->_service);

		$content['search_form'] = $this->_form_builder->getForm($search);

		//Table
		$table_instance = new LocateUsTypeTableForm($this->_service,
			$search->getFilterQuery());

		$content['table'] = $this->_form_builder->getForm($table_instance);

		//Pagination
		$content['pager'] = [
			'#type' => 'pager',
		];

		return $content;
	}

	/**
	 * LocateUsType add form
	 *
	 * @return mixed
	 */
	public function add(){
		$locate_us_type = new LocateUsType();
		$form           = new LocateUsTypeForm($this->_service, $locate_us_type);

		$build['form'] = $this->_form_builder->getForm($form);

		return $build;
	}

	/**
	 * LocateUsType edit form
	 *
	 * @param int $id
	 *
	 * @return \Drupal\singpost_toolbox_locate_us\Form\LocateUsTypeForm
	 */
	public function edit(int $id){
		$locate_us_type = LocateUsType::findOrFail($id);
		$form           = new LocateUsTypeForm($this->_service, $locate_us_type);
		$build['form']  = $this->_form_builder->getForm($form);

		return $build;
	}

	/**
	 * LocateUsType delete form
	 *
	 * @param int $id
	 *
	 * @return mixed
	 */
	public function delete(int $id){
		$locate_us_type = LocateUsType::findOrFail($id);
		$form           = new LocateUsTypeDeleteForm($locate_us_type);
		$build['form']  = $this->_form_builder->getForm($form);

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

		$locate_us_type = LocateUsType::findOrFail($id);

		return new JsonResponse($locate_us_type->updateAttributes(['status' => $status]));
	}
}