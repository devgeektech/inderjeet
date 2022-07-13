<?php


namespace Drupal\singpost_toolbox_calculate_postage\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBuilder;
use Drupal\singpost_toolbox_calculate_postage\Form\Dimension\DimensionDeleteForm;
use Drupal\singpost_toolbox_calculate_postage\Form\Dimension\DimensionForm;
use Drupal\singpost_toolbox_calculate_postage\Form\Dimension\DimensionSearchForm;
use Drupal\singpost_toolbox_calculate_postage\Form\Dimension\DimensionTableForm;
use Drupal\singpost_toolbox_calculate_postage\Model\Dimension;
use Drupal\singpost_toolbox_calculate_postage\Repositories\DimensionRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class DimensionController
 *
 * @package Drupal\singpost_toolbox_calculate_postage\Controller
 */
class DimensionController extends ControllerBase{

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
	 * Dimension model instance
	 *
	 * @var DimensionRepository
	 */
	protected $_service;


	/**
	 * The Form builder.
	 *
	 * @param FormBuilder $form_builder
	 * @param Request $request
	 * @param DimensionRepository $dimension
	 */
	public function __construct(
		FormBuilder $form_builder,
		Request $request,
		DimensionRepository $dimension){
		$this->_form_builder = $form_builder;
		$this->_request      = $request;
		$this->_service      = $dimension;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function create(ContainerInterface $container){
		return new static($container->get('form_builder'),
			$container->get('request_stack')->getCurrentRequest(),
			$container->get('singpost.toolbox.calculate.service.dimension'));
	}

	/**
	 * Dimension listing table
	 *
	 * @return mixed
	 */
	public function index(){

		//Filter
		$search = new DimensionSearchForm($this->_request, $this->_service);

		$content['search_form'] = $this->_form_builder->getForm($search);

		//Table
		$table_instance = new DimensionTableForm($this->_service,
			$search->getFilterQuery());

		$content['table'] = $this->_form_builder->getForm($table_instance);

		//Pagination
		$content['pager'] = [
			'#type' => 'pager',
		];

		return $content;
	}

	/**
	 * Dimension add form
	 *
	 * @return mixed
	 */
	public function add(){
		$dimension = new Dimension();
		$form      = new DimensionForm($this->_service, $dimension);

		$build['form'] = $this->_form_builder->getForm($form);

		return $build;
	}

	/**
	 * Dimension edit form
	 *
	 * @param int $id
	 *
	 * @return \Drupal\singpost_toolbox_calculate_postage\Form\Dimension\DimensionForm
	 */
	public function edit(int $id){
		$dimension     = Dimension::findOrFail($id);
		$form          = new DimensionForm($this->_service, $dimension);
		$build['form'] = $this->_form_builder->getForm($form);

		return $build;
	}

	/**
	 * Dimension delete form
	 *
	 * @param int $id
	 *
	 * @return mixed
	 */
	public function delete(int $id){
		$dimension     = Dimension::findOrFail($id);
		$form          = new DimensionDeleteForm($dimension);
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

		$dimension = Dimension::findOrFail($id);

		return new JsonResponse($dimension->updateAttributes(['published' => $status]));
	}
}