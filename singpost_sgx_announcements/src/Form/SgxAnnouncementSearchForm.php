<?php


namespace Drupal\singpost_sgx_announcements\Form;


use Drupal\Core\Form\FormStateInterface;
use Drupal\singpost_base\Form\BaseSearchForm;
use Drupal\singpost_sgx_announcements\Repositories\SgxAnnouncementRepository;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class SgxAnnouncementSearchForm
 *
 * @package Drupal\singpost_sgx_announcement\Form
 */
class SgxAnnouncementSearchForm extends BaseSearchForm{

	/*
	 * @var SgxAnnouncementRepository
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
		return 'sgx_announcement_search_form';
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
			'#title'      => t('Search'),
			'#attributes' => ['class' => ['form--inline clearfix']],
		];

		return parent::buildForm($form, $form_state);
	}

	/**
	 * @param array $default_values
	 *
	 * @return array|mixed
	 */
	public function searchFilters($default_values = []){
		$filters['title'] = [
			'#type'          => 'search',
			'#title'         => t('Title'),
			'#placeholder'   => t('Search by title'),
			'#default_value' => !empty($default_values['title']) ? $default_values['title'] : '',
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
			'#default_value' => $default_values['status'] ?? '',
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