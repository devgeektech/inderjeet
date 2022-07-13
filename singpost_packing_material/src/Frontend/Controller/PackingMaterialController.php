<?php


namespace Drupal\singpost_packing_material\Frontend\Controller;


use Drupal;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBuilder;
use Drupal\singpost_packing_material\Form\Config\PackingMaterialConfigForm;
use Drupal\singpost_packing_material\Frontend\Form\OrderForm;
use Drupal\singpost_packing_material\Model\PackingMaterialOrder;
use Drupal\singpost_packing_material\Model\PackingMaterialOrderDetail;
use Drupal\singpost_packing_material\Model\PackingMaterialProduct;
use Drupal\singpost_packing_material\Repositories\CategoryRepository;
use Drupal\singpost_packing_material\Repositories\ProductRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Url;

/**
 * Class PackingMaterialController
 *
 * @package Drupal\singpost_packing_material\Frontend\Controller
 */
class PackingMaterialController extends ControllerBase{

	/**
	 * @var \Drupal\singpost_packing_material\Repositories\CategoryRepository
	 */
	protected $_category;

	/**
	 * @var \Drupal\singpost_packing_material\Repositories\ProductRepository
	 */
	protected $_product;

	/**
	 * @var \Symfony\Component\HttpFoundation\Request
	 */
	protected $_request;

	/**
	 * @var \Drupal\Core\Form\FormBuilder
	 */
	protected $_form_builder;

	/**
	 * PackingMaterialController constructor.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @param \Drupal\Core\Form\FormBuilder $form_builder
	 * @param \Drupal\singpost_packing_material\Repositories\CategoryRepository $category_repository
	 * @param \Drupal\singpost_packing_material\Repositories\ProductRepository $product_repository
	 */
	public function __construct(
		Request $request,
		FormBuilder $form_builder,
		CategoryRepository $category_repository,
		ProductRepository $product_repository
	){
		$this->_request      = $request;
		$this->_form_builder = $form_builder;
		$this->_category     = $category_repository;
		$this->_product      = $product_repository;

	}

	/**
	 * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
	 *
	 * @return \Drupal\Core\Controller\ControllerBase|\Drupal\singpost_packing_material\Frontend\Controller\PackingMaterialController
	 */
	public static function create(ContainerInterface $container){
		return new static($container->get('request_stack')->getCurrentRequest(),
			$container->get('form_builder'),
			$container->get('singpost.pm.category.service'),
			$container->get('singpost.pm.product.service'));
	}

	/**
	 * @return array
	 */
	public function index(){
		$categories = $this->_category->getAllProduct();
		$config     = Drupal::config(PackingMaterialConfigForm::$config_name);
		$block 		= \Drupal\block\Entity\Block::load('singpostd9_pm_navigation');
		$render_b 	=  $block->get('settings');

		$cat_array = array();
		foreach ($categories as $key => $value) {
			# code...
			$category_merge  = 'rigid envelopes';
			$category_merge_1 = 'padded envelopes';
			if(strtolower($value['category_name']) == $category_merge){
				continue;
			}else if (strtolower($value['category_name']) == $category_merge_1) {
				# code...
				$cat_array['category_name'][] = 'Padded and Rigid envelopes';
			}else{
				$cat_array['category_name'][] = $value['category_name'];
			}
		}
 
		// $categories['new_category'] = $cat_array;
		return [
			'#theme' => 'singpost_pm',
			'#data'  => [
				'categories' => $categories,
				'new_category' => $cat_array,
				'notice'     => $config->get('pm_notice'),
				'config'     => $config, 
				'title'     => $render_b
			],
			'#cache' => [
				'max-age' => 0
			]
		];
	}

	/**
	 * @return array
	 */
	public function confirm(){
		$config = Drupal::config(PackingMaterialConfigForm::$config_name);

		return [
			'#theme' => 'singpost_confirmation',
			'#data'  => [
				'content' => $config->get('pm_confirmation_page')
			],
			'#cache' => [
				'max-age' => 0
			]
		];
	}

	/**
	 * @return array
	 */
	public function singleProduct($id){
 		
 		//$categories = $this->_category->getAllProduct();
		$product    = $this->_category->getSingleProductById($id);
		$ref_url_string = \Drupal::request()->headers->get('referer');
		//print_r(Json::encode($product));
		if (!empty($product)){
			return [
				'#theme' => 'singpost_pm_single_product',
				//'#title' => t('singpost single product'.$id),
				 
				'#data'  => [
					'data_check' => true,
					'url_back'  => $ref_url_string,
					'product' => $product[0] 
				],
				'#cache' => [
					'max-age' => 0
				]
			];
		}else{
			return [
				'#theme' => 'singpost_pm_single_product',
				//'#title' => t('singpost single product'.$id),
				 
				'#data'  => [
					'data_check' => false,
					'product' => ''  
				],
				'#cache' => [
					'max-age' => 0
				]
			];
		}
	}


	/**
	 * @return array
     */
	public function cartSummary(){
 		$ref_url_string = \Drupal::request()->headers->get('referer');

 		$block 		= \Drupal\block\Entity\Block::load('singpostd9_pm_navigation');
		$render_b 	=  $block->get('settings');
 		return [

				'#theme' => 'singpost_pm_cart_summary',
				//'#title' => t('singpost single product'.$id),
				 
				'#data'  => [
					'data_check' => true ,
					'url_back'  => $ref_url_string,
					'title' => $render_b
				],
				'#cache' => [
					'max-age' => 0
				]
			];
	}

		/**
	 * @return array
     */
	public function cartCheckout(){
 		$order  = new PackingMaterialOrder();
		$detail = new PackingMaterialOrderDetail();

		$order_form = new OrderForm($order, $detail, $this->_request);
		$form       = $this->_form_builder->getForm($order_form);
		$ref_url_string = \Drupal::request()->headers->get('referer');
 		return [

				'#theme' => 'singpost_pm_cart_checkout',
				//'#title' => t('singpost single product'.$id),
				 
				'#data'  => [
					'data_check' => true ,
					'url_back'  => $ref_url_string,
					'form'       => $form
				],
				'#attached' => [
					'library' => [
						'singpost_toolbox/toolbox',
						'singpost_toolbox/recaptcha'
					]
				],
				'#cache' => [
					'max-age' => 0
				]
			];
	}


		/**
	 * @return array
     */
	public function cartThanks($id){
       if (empty($id)) {
       	    //$confirm = Url::fromRoute('singpost.pm.index');
			return $this->redirect('singpost.pm.index');
			 
       }
		$ref_url_string = \Drupal::request()->headers->get('referer');

 		return [

				'#theme' => 'singpost_pm_cart_thanks',
				//'#title' => t('singpost single product'.$id),
				 
				'#data'  => [
					'data_check' => true ,
					'order_id'       => $id,
					'url_url_backback'  => $ref_url_string
				],
				'#cache' => [
					'max-age' => 0
				]
			];
	}

}