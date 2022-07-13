<?php


namespace Drupal\singpost_toolbox_calculate_postage\Controller;


use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBuilder;
use Drupal\singpost_toolbox_calculate_postage\Form\DeliveryService\DeliveryServiceDeleteForm;
use Drupal\singpost_toolbox_calculate_postage\Form\DeliveryService\DeliveryServiceForm;
use Drupal\singpost_toolbox_calculate_postage\Form\DeliveryService\DeliveryServiceSearchForm;
use Drupal\singpost_toolbox_calculate_postage\Form\DeliveryService\DeliveryServiceTableForm;
use Drupal\singpost_toolbox_calculate_postage\Model\DeliveryServiceModel;
use Drupal\singpost_toolbox_calculate_postage\Repositories\DeliveryServiceRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class DeliveryServiceController
 *
 * @package Drupal\singpost_toolbox_calculate_postage\Controller
 */
class DeliveryServiceController extends ControllerBase{

	/**
	 * @var \Drupal\Core\Form\FormBuilder
	 */
	protected $_form_builder;

	/**
	 * @var \Symfony\Component\HttpFoundation\Request
	 */
	protected $_request;

	/**
	 * @var \Drupal\singpost_toolbox_calculate_postage\Repositories\DeliveryServiceRepository
	 */
	protected $_repository;

	/**
	 * DeliveryServiceController constructor.
	 *
	 * @param \Drupal\Core\Form\FormBuilder $form_builder
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @param \Drupal\singpost_toolbox_calculate_postage\Repositories\DeliveryServiceRepository $repository
	 */
	public function __construct(
		FormBuilder $form_builder,
		Request $request,
		DeliveryServiceRepository $repository){
		$this->_form_builder = $form_builder;
		$this->_request      = $request;
		$this->_repository   = $repository;
	}

	/**
	 * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
	 *
	 * @return \Drupal\Core\Controller\ControllerBase|\Drupal\singpost_toolbox_calculate_postage\Controller\DeliveryServiceController
	 */
	public static function create(ContainerInterface $container){
		return new static($container->get('form_builder'),
			$container->get('request_stack')->getCurrentRequest(),
			$container->get('singpost.delivery_service.service'));
	}

	/**
	 * @return mixed
	 */
	public function index(){
		$search = new DeliveryServiceSearchForm($this->_request, $this->_repository);
		$form   = new DeliveryServiceTableForm($search->getFilterQuery(), $this->_repository);

		$build['search_form'] = $this->_form_builder->getForm($search);
		$build['table']       = $this->_form_builder->getForm($form);

		$build['pager'] = [
			'#type' => 'pager',
		];

		return $build;
	}

	/**
	 * @return mixed
	 */
	public function add(){
		$model = new DeliveryServiceModel();
		$form  = new DeliveryServiceForm($model, $this->_repository);

		$build['form'] = $this->_form_builder->getForm($form);

		return $build;
	}

	/**
	 * @param $id
	 *
	 * @return mixed
	 */
	public function edit($id){
		$model = DeliveryServiceModel::findOrFail($id);
		$form  = new DeliveryServiceForm($model, $this->_repository);

		$build['form'] = $this->_form_builder->getForm($form);

		return $build;
	}

	/**
	 * @param $id
	 *
	 * @return mixed
	 */
	public function delete($id){
		$model = DeliveryServiceModel::findOrFail($id);
		$form  = new DeliveryServiceDeleteForm($model);

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

		$model = DeliveryServiceModel::findOrFail($id);

		return new JsonResponse($model->updateAttributes(['published' => $status]));
	}
}