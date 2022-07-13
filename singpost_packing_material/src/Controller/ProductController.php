<?php


namespace Drupal\singpost_packing_material\Controller;


use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBuilder;
use Drupal\singpost_packing_material\Form\Product\ProductDeleteForm;
use Drupal\singpost_packing_material\Form\Product\ProductForm;
use Drupal\singpost_packing_material\Form\Product\ProductSearchForm;
use Drupal\singpost_packing_material\Form\Product\ProductTableForm;
use Drupal\singpost_packing_material\Model\PackingMaterialProduct;
use Drupal\singpost_packing_material\Repositories\CategoryRepository;
use Drupal\singpost_packing_material\Repositories\ProductRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ProductController
 *
 * @package Drupal\singpost_packing_material\Controller
 */
class ProductController extends ControllerBase{

	/**
	 * @var \Symfony\Component\HttpFoundation\Request
	 */
	protected $_request;

	/**
	 * @var \Drupal\singpost_packing_material\Repositories\ProductRepository
	 */
	protected $_product;

	/**
	 * @var \Drupal\singpost_packing_material\Repositories\CategoryRepository
	 */
	protected $_category;

	/**
	 * @var \Drupal\Core\Form\FormBuilder
	 */
	protected $_form_builder;

	/**
	 * ProductController constructor.
	 *
	 * @param \Drupal\Core\Form\FormBuilder $form_builder
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @param \Drupal\singpost_packing_material\Repositories\ProductRepository $product
	 * @param \Drupal\singpost_packing_material\Repositories\CategoryRepository $category
	 */
	public function __construct(
		FormBuilder $form_builder,
		Request $request,
		ProductRepository $product,
		CategoryRepository $category){
		$this->_form_builder = $form_builder;
		$this->_request      = $request;
		$this->_product      = $product;
		$this->_category     = $category;
	}

	/**
	 * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
	 *
	 * @return \Drupal\Core\Controller\ControllerBase|\Drupal\singpost_packing_material\Controller\ProductController
	 */
	public static function create(ContainerInterface $container){
		return new static($container->get('form_builder'),
			$container->get('request_stack')->getCurrentRequest(),
			$container->get('singpost.pm.product.service'),
			$container->get('singpost.pm.category.service'));
	}

	/**
	 * @return mixed
	 */
	public function index(){
		$search = new ProductSearchForm($this->_request, $this->_category, $this->_product);
		$table  = new ProductTableForm($this->_product, $this->_category,
			$search->getFilterQuery());

		$build['search_form'] = $this->_form_builder->getForm($search);
		$build['table']       = $this->_form_builder->getForm($table);

		$build['pager'] = ['#type' => 'pager'];

		return $build;
	}

	/**
	 * @return array|mixed|\Symfony\Component\HttpFoundation\Response
	 */
	public function add(){
		$product = new PackingMaterialProduct();
		$form    = new ProductForm($this->_category, $this->_product, $product);

		$build['form'] = $this->_form_builder->getForm($form);

		return $build;
	}

	/**
	 * @param int $id
	 *
	 * @return array|mixed|\Symfony\Component\HttpFoundation\Response
	 */
	public function edit(int $id){
		$product = PackingMaterialProduct::findOrFail($id);
		$form    = new ProductForm($this->_category, $this->_product, $product);

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

		$product = PackingMaterialProduct::findOrFail($id);

		return new JsonResponse($product->updateAttributes(['published' => $status]));
	}

	/**
	 * @param $id
	 *
	 * @return array|mixed|\Symfony\Component\HttpFoundation\Response
	 */
	public function delete($id){
		$product = PackingMaterialProduct::findOrFail($id);
		$form    = new ProductDeleteForm($product);

		$build['form'] = $this->_form_builder->getForm($form);

		return $build;
	}
}