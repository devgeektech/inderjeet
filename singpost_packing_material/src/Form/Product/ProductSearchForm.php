<?php


namespace Drupal\singpost_packing_material\Form\Product;


use Drupal\Core\Form\FormStateInterface;
use Drupal\singpost_base\Form\BaseSearchForm;
use Drupal\singpost_packing_material\Repositories\CategoryRepository;
use Drupal\singpost_packing_material\Repositories\ProductRepository;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ProductSearchForm
 *
 * @package Drupal\singpost_packing_material\Form\Product
 */
class ProductSearchForm extends BaseSearchForm{

	/**
	 * @var \Drupal\singpost_packing_material\Repositories\CategoryRepository
	 */
	protected $_category;

	/**
	 * @var \Drupal\singpost_packing_material\Repositories\ProductRepository
	 */
	protected $_product;

	/**
	 * ProductSearchForm constructor.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @param \Drupal\singpost_packing_material\Repositories\CategoryRepository $category
	 * @param \Drupal\singpost_packing_material\Repositories\ProductRepository $product
	 */
	public function __construct(
		Request $request,
		CategoryRepository $category,
		ProductRepository $product){
		parent::__construct($request);
		$this->_category = $category;
		$this->_product  = $product;
	}

	/**
	 * @return string
	 */
	public function getFormId(){
		return 'pm_product_search_form';
	}

	/**
	 * @param array $values
	 *
	 * @return array|mixed
	 */
	public function searchFilters($values = []){
		$filters['title'] = [
			'#type'          => 'search',
			'#title'         => t('Title'),
			'#placeholder'   => t('Search by title'),
			'#default_value' => !empty($values['title']) ? $values['title'] : '',
			'#size'          => 30,
			'field'          => 'title',
			'condition'      => 'LIKE',
		];

		$filters['category'] = [
			'#type'          => 'select',
			'#title'         => t('Category'),
			'#attributes'    => [
				'class' => ['select2']
			],
			'#default_value' => $values['category'] ?? '',
			'#empty_option'  => 'Select category',
			'#options'       => $this->_category->getCategories(),
			'field'          => 'category_id',
			'condition'      => '='
		];

		$filters['status'] = [
			'#type'          => 'select',
			'#title'         => t('Status'),
			'#attributes'    => [
				'class' => ['select2']
			],
			'#default_value' => $values['status'] ?? '',
			'#options'       => [
				'' => t('All'),
				1  => t('Published'),
				0  => t('Unpublished')
			],
			'field'          => 'published',
			'condition'      => '='
		];

		return $filters;
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 *
	 * @return array
	 */
	public function buildForm(array $form, FormStateInterface $form_state){
		$form['filters'] = [
			'#type'       => 'container',
			'#title'      => t('Search Product'),
			'#attributes' => ['class' => ['form--inline clearfix']],
		];

		return parent::buildForm($form, $form_state);
	}
}