<?php


namespace Drupal\singpost_announcements\Frontend\Form;


use Drupal\Core\Form\FormStateInterface;
use Drupal\singpost_announcements\Repositories\AnnouncementRepository;
use Drupal\singpost_base\Form\BaseSearchForm;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AnnouncementFilterForm
 *
 * @package Drupal\singpost_announcements\Frontend\Form
 */
class AnnouncementFilterForm extends BaseSearchForm{

	/**
	 * @var \Drupal\singpost_announcements\Repositories\AnnouncementRepository
	 */
	protected $_announcement;

	/**
	 * AnnouncementFilterForm constructor.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @param \Drupal\singpost_announcements\Repositories\AnnouncementRepository $repository
	 */
	public function __construct(Request $request, AnnouncementRepository $repository){
		parent::__construct($request);
		$this->_announcement = $repository;
	}

	/**
	 * @return string
	 */
	public function getFormId(){
		return 'announcement_filter_form';
	}

	/**
	 * @param array $values
	 *
	 * @return array|void
	 */
	public function searchFilters($values = []){
		$filters['year'] = [
			'field'          => 'YEAR(FROM_UNIXTIME(' . $this->_announcement->getModel()::tableAlias() . ".start_date))",
			'condition'      => '=',
			'#title'         => NULL,
			'#type'          => 'select',
			'#default_value' => !empty($values['year']) ? $values['year'] : '',
			'#options'       => $this->_announcement->getYears(),
			'#empty_option'  => t('Browse by Year'),
			'#attributes'    => [
				'onchange' => 'this.form.submit()']
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
			'class' => ['form-filter', 'announcement-filter', 'd-lg-flex', 'justify-content-lg-end']
		];

		unset($form['filters']['actions']['reset']);
		$form['filters']['actions']['submit'] = [
			'#type'       => 'submit',
			'#value'      => t('Search'),
			'#attributes' => [
				'class' => ['hidden']
			],
		];

		return $form;
	}

	/**
	 * @return array
	 */
	public function getFilterQuery(){
		$where = ['published = ?'];
		$args  = [1];

		return $this->parseQuery($where, $args);
	}
}