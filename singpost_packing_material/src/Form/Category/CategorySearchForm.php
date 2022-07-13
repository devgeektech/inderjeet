<?php


namespace Drupal\singpost_packing_material\Form\Category;


use Drupal\Core\Form\FormStateInterface;
use Drupal\singpost_base\Form\BaseSearchForm;
use Drupal\singpost_packing_material\Repositories\CategoryRepository;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CategorySearchForm
 *
 * @package Drupal\singpost_packing_material\Form\Category
 */
class CategorySearchForm extends BaseSearchForm{

	/**
	 * @var \Drupal\singpost_packing_material\Repositories\CategoryRepository
	 */
	protected $_category;

	/**
	 * CategorySearchForm constructor.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @param \Drupal\singpost_packing_material\Repositories\CategoryRepository $repository
	 */
	public function __construct(Request $request, CategoryRepository $repository){
		parent::__construct($request);
		$this->_category = $repository;
	}

	/**
	 * @return string
	 */
	public function getFormId(){
		return 'pm_category_search_form';
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
			'#title'      => t('Search Category'),
			'#attributes' => ['class' => ['form--inline clearfix']],
		];

		return parent::buildForm($form, $form_state);
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
}