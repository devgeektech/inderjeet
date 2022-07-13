<?php


namespace Drupal\singpost_publications\Frontend\Form;


use Drupal\Core\Form\FormStateInterface;
use Drupal\singpost_base\Form\BaseSearchForm;
use Drupal\singpost_publications\Repositories\PublicationRepository;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class PublicationFilterForm
 *
 * @package Drupal\singpost_publications\Frontend\Form
 */
class PublicationFilterForm extends BaseSearchForm{

	/**
	 * @var \Drupal\singpost_publications\Repositories\PublicationRepository
	 */
	protected $_publication;

	/**
	 * PublicationFilterForm constructor.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @param \Drupal\singpost_publications\Repositories\PublicationRepository $publication_repository
	 */
	public function __construct(Request $request, PublicationRepository $publication_repository){
		parent::__construct($request);
		$this->_publication = $publication_repository;
	}

	/**
	 * @return string
	 */
	public function getFormId(){
		return 'publication_filter_form';
	}

	/**
	 * @param array $values
	 *
	 * @return array|void
	 */
	public function searchFilters($values = []){
		$options = $this->_publication->getYears();
		$filters['sorting'] = [
			'#title'         => t('Sort By:'),
			'#type'          => 'select',
			'#default_value' => !empty($values['sorting']) ? $values['sorting'] : '',
			'#options'       => [
				'asc' => 'Oldest',
				'desc' => 'Latest'
			],
			'#empty_option'  => t('Sorting'),
			'#attributes'    => [
				'onchange' => 'this.form.submit()',
				'class' => ['sgp-select']
			]
		];

		$filters['year'] = [
			'field'          => 'published_at',
			'condition'      => '=',
			'#title'         => NULL,
			'#type'          => 'select',
			'#default_value' => !empty($values['year']) ? $values['year'] : [],
			'#empty_option'  => t('Select Year'),
			'#options'       => $options,
			'#prefix'        => '<div class="mr-lg-3">',
			'#suffix'        => '</div>',
			'#attributes'    => [
				'class'    => ['sgp-select'],
				'onchange' => 'this.form.submit()']
		];

	/*	$filters['an_category'] = [
			'field'          => 'sustainability_report',
			'condition'      => 'IS',
 			'#type'          => 'select',
			'#default_value' => !empty($values['an_category']) ? $values['an_category'] : '',
			'#options'       => [
				'1' => 'Annual reports',
				'2' => 'Sustainability reports'
			],
			'#empty_option'  => t('Category filter'),
			'#attributes'    => [
				'onchange' => 'this.form.submit()',
				'class' => ['sgp-select']
			]
		]; */

		$filters['search'] = [
			'#type'          => 'search',
			'#title'         => NULL,
			'#placeholder'   => t('Search by keywords'),
			'#default_value' => '',
			'#size'          => 48,
			'field'          => 'title',
			'condition'      => 'LIKE',
			'#attributes'    => [
				'class' => ['sgp-input-text']
			]
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
		$form = parent::buildForm($form, $form_state);

		$form['#attributes'] = [
			'class' => ['form-filter', 'sgp-sort']
		];

		unset($form['filters']['actions']['reset']);
		$form['filters']['actions']['submit'] = [
			'#type'       => 'submit',
			'#value'      => t('Search'),
			'#attributes' => [
				'class' => ['sgp-search-input__btn']
			],
		];

		return $form;
	}

	public function getFilterQuery(){
		$where = ['published = ?'];
		$args  = [1];
		$sort_args = 'desc';
		return $this->parseQuery($where, $args, $sort_args);
	}
}