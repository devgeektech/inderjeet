<?php


namespace Drupal\singpost_packing_material\Controller;


use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBuilder;
use Drupal\singpost_packing_material\Form\Order\OrderSearchForm;
use Drupal\singpost_packing_material\Form\Order\OrderTableForm;
use Drupal\singpost_packing_material\Form\Order\OrderViewForm;
use Drupal\singpost_packing_material\Model\PackingMaterialOrder;
use Drupal\singpost_packing_material\Repositories\OrderRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class OrderController
 *
 * @package Drupal\singpost_packing_material\Controller
 */
class OrderController extends ControllerBase{

	/**
	 * @var \Drupal\Core\Form\FormBuilder
	 */
	protected $_form_builder;

	/**
	 * @var \Drupal\singpost_packing_material\Repositories\OrderRepository
	 */
	protected $_service;

	/**
	 * @var \Symfony\Component\HttpFoundation\Request
	 */
	protected $_request;

	/**
	 * OrderController constructor.
	 *
	 * @param \Drupal\Core\Form\FormBuilder $form_builder
	 * @param \Drupal\singpost_packing_material\Repositories\OrderRepository $repository
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 */
	public function __construct(
		FormBuilder $form_builder,
		OrderRepository $repository,
		Request $request){
		$this->_form_builder = $form_builder;
		$this->_service      = $repository;
		$this->_request      = $request;
	}

	/**
	 * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
	 *
	 * @return \Drupal\Core\Controller\ControllerBase|\Drupal\singpost_packing_material\Controller\OrderController
	 */
	public static function create(ContainerInterface $container){
		return new static($container->get('form_builder'),
			$container->get('singpost.pm.order.service'),
			$container->get('request_stack')->getCurrentRequest());
	}

	/**
	 * @return mixed
	 */
	public function index(){
		$search = new OrderSearchForm($this->_request, $this->_service);
		$form   = new OrderTableForm($this->_service, $search->getFilterQuery());

		$build['search'] = $this->_form_builder->getForm($search);
		$build['table']  = $this->_form_builder->getForm($form);

		$build['pager'] = [
			'#type' => 'pager'
		];

		return $build;
	}

	/**
	 * @param $id
	 *
	 * @return mixed
	 */
	public function view($id){
		$order = PackingMaterialOrder::findOrFail($id);
		$form  = new OrderViewForm($order);

		$build['form'] = $this->_form_builder->getForm($form);

		return $build;
	}
}