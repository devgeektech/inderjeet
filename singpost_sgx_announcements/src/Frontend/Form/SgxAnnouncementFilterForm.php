<?php


namespace Drupal\singpost_sgx_announcements\Frontend\Form;


use Drupal\Core\Form\FormStateInterface;
use Drupal\singpost_base\Form\BaseSearchForm;
use Drupal\singpost_sgx_announcements\Repositories\SgxAnnouncementRepository;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class SgxAnnouncementFilterForm
 *
 * @package Drupal\singpost_sgx_announcements\Frontend\Form
 */
class SgxAnnouncementFilterForm extends BaseSearchForm{

	/**
	 * @var \Drupal\singpost_sgx_announcements\Repositories\SgxAnnouncementRepository
	 */
	protected $_sgx_announcement;

	/**
	 * SgxAnnouncementSearchForm constructor.
	 *
	 * @param Request $request
	 * @param SgxAnnouncementRepository $sgx_announcement
	 */
	public function __construct(
		Request $request,
		SgxAnnouncementRepository $sgx_announcement){
		parent::__construct($request);
		$this->_sgx_announcement = $sgx_announcement;
	}

	/**
	 * @return string
	 */
	public function getFormId(){
		return 'sgx_announcement_filter_form';
	}

	/**
	 * @param array $values
	 *
	 * @return array|mixed
	 */
	public function searchFilters($values = []){

		$filters['sorting'] = [
			'#title'         => t('Sort By'),
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
			'field'          => 'YEAR(FROM_UNIXTIME(' . $this->_sgx_announcement->getModel()
			                                                                    ->tableName() . ".date))",
			'condition'      => '=',
			'#title'         => NULL,
			'#type'          => 'select',
			'#default_value' => !empty($values['year']) ? $values['year'] : '',
			'#options'       => $this->_sgx_announcement->getYears(),
			'#empty_option'  => t('Select Year'),
			'#prefix'        => '<div class="mr-lg-3">',
			'#suffix'        => '</div>',
			'#attributes'    => [
				'onchange' => 'this.form.submit()',
				'class' => ['sgp-select']
			]
		];

		$filters['month'] = [
			'field'          => 'MONTH(FROM_UNIXTIME(' . $this->_sgx_announcement->getModel()
			                                                                     ->tableName() . ".date))",
			'condition'      => '=',
			'#title'         => NULL,
			'#type'          => 'select',
			'#default_value' => !empty($values['month']) ? $values['month'] : '',
			'#options'       => !empty($values['year']) ? $this->_sgx_announcement->getListMonthOfYear($values['year']) : [],
			'#empty_option'  => t('All Months'),
			'#states'        => [
				'invisible' => [
					'select[name="year"]' => ['value' => 'All'],
				],
			],
			'#attributes'    => [
				'onchange' => 'this.form.submit()',
				'class' => ['sgp-select']
			]
		];

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
		$form['search_form'] = [
			'#theme'            	    => 'singpost_sgx_announcement_filter',
			'#sgx_announcements_filter' => $form,
		];
		return $form;
	}

	/**
	 * @return array
	 */
	public function getFilterQuery(){
		$where = ['published = ?'];
		$args  = [1];
		$sort_args = 'desc';
		return $this->parseQuery($where, $args, $sort_args);
	}
}