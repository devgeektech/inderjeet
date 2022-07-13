<?php


namespace Drupal\singpost_packing_material\Controller;


use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBuilder;
use Drupal\singpost_packing_material\Form\Category\CategoryDeleteForm;
use Drupal\singpost_packing_material\Form\Category\CategoryForm;
use Drupal\singpost_packing_material\Form\Category\CategorySearchForm;
use Drupal\singpost_packing_material\Form\Category\CategoryTableForm;
use Drupal\singpost_packing_material\Model\PackingMaterialCategory;
use Drupal\singpost_packing_material\Repositories\CategoryRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CategoryController
 *
 * @package Drupal\singpost_packing_material\Controller
 */
class CategoryController extends ControllerBase{

	/**
	 * @var \Drupal\Core\Form\FormBuilder
	 */
	protected $_form_builder;

	/**
	 * @var \Symfony\Component\HttpFoundation\Request
	 */
	protected $_request;

	/**
	 * @var \Drupal\singpost_packing_material\Repositories\CategoryRepository
	 */
	protected $_service;

	/**
	 * CategoryController constructor.
	 *
	 * @param \Drupal\Core\Form\FormBuilder $form_builder
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @param \Drupal\singpost_packing_material\Repositories\CategoryRepository $repository
	 */
	public function __construct(
		FormBuilder $form_builder,
		Request $request,
		CategoryRepository $repository){
		$this->_form_builder = $form_builder;
		$this->_request      = $request;
		$this->_service      = $repository;
	}

	/**
	 * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
	 *
	 * @return \Drupal\Core\Controller\ControllerBase|\Drupal\singpost_packing_material\Controller\CategoryController
	 */
	public static function create(ContainerInterface $container){
		return new static($container->get('form_builder'),
			$container->get('request_stack')->getCurrentRequest(),
			$container->get('singpost.pm.category.service'));
	}

	/**
	 * @return mixed
	 */
	public function index(){
		//search filter
		$search_form = new CategorySearchForm($this->_request, $this->_service);

		$content['search_form'] = $this->_form_builder->getForm($search_form);

		//table
		$table_instance   = new CategoryTableForm($this->_service, $search_form->getFilterQuery());
		$content['table'] = $this->_form_builder->getForm($table_instance);

		//pagination
		$content['pager'] = [
			'#type' => 'pager',
		];

		return $content;
	}

	/**
	 * @return mixed
	 */
	public function add(){
		$category = new PackingMaterialCategory();
		$form     = new CategoryForm($this->_service, $category);

		$build['form'] = $this->_form_builder->getForm($form);

		return $build;
	}

	/**
	 * @param int $id
	 *
	 * @return mixed
	 */
	public function edit(int $id){
		$category      = PackingMaterialCategory::findOrFail($id);
		$form          = new CategoryForm($this->_service, $category);
		$build['form'] = $this->_form_builder->getForm($form);

		return $build;
	}

	/**
	 * @param int $id
	 *
	 * @return mixed
	 */
	public function delete(int $id){
		$category      = PackingMaterialCategory::findOrFail($id);
		$form          = new CategoryDeleteForm($category);
		$build['form'] = $this->_form_builder->getForm($form);

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

		$category = PackingMaterialCategory::findOrFail($id);

		return new JsonResponse($category->updateAttributes(['published' => $status]));
	}
}