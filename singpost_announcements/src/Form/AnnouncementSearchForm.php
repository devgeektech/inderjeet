<?php

namespace Drupal\singpost_announcements\Form;

use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Form\FormStateInterface;
use Drupal\singpost_announcements\Repositories\AnnouncementRepository;
use Drupal\singpost_base\Form\BaseSearchForm;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AnnouncementSearchForm
 *
 * @package Drupal\singpost_announcements\Form
 */
class AnnouncementSearchForm extends BaseSearchForm{

	/**
	 * @var AnnouncementRepository
	 */
	protected $_announcement;

	/**
	 * @var \Drupal\Core\Datetime\DateFormatter
	 */
	protected $_dateformat;

	/**
	 * AnnouncementSearchForm constructor.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @param \Drupal\Core\Datetime\DateFormatter $dateformat
	 * @param \Drupal\singpost_announcements\Repositories\AnnouncementRepository $announcement
	 */
	public function __construct(
		Request $request,
		DateFormatter $dateformat,
		AnnouncementRepository $announcement){
		parent::__construct($request);

		$this->_announcement = $announcement;
		$this->_dateformat   = $dateformat;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFormId(){
		return 'announcement_search_form';
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildForm(array $form, FormStateInterface $form_state){
		$form['#attached']['library'][] = 'singpost_base/datetimepicker';

		$form['filters'] = [
			'#type'       => 'container',
			'#title'      => t('Search Announcements'),
			'#attributes' => ['class' => ['form--inline clearfix']],
		];

		return parent::buildForm($form, $form_state);
	}

	/**
	 * @inheritDoc
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

		$filters['start_date'] = [
			'#type'          => 'textfield',
			'#title'         => t('Start Date'),
			'#attributes'    => [
				'class'    => ['calendar start-date'],
				'readonly' => TRUE
			],
			'#default_value' => !empty($default_values['start_date']) ? $this->_dateformat->format(
				$default_values['start_date'], 'custom', 'd-m-Y G:i') : '',
			'#size'          => 30,
			'#placeholder'   => t('From date'),
			'field'          => 'start_date',
			'condition'      => '>=',
		];

		$filters['end_date'] = [
			'#type'          => 'textfield',
			'#title'         => t('End Date'),
			'#attributes'    => [
				'class'    => ['calendar end-date'],
				'readonly' => TRUE
			],
			'#default_value' => !empty($default_values['end_date']) ? $this->_dateformat->format(
				$default_values['end_date'], 'custom', 'd-m-Y G:i') : '',
			'#size'          => 30,
			'#placeholder'   => t('To date'),
			'field'          => 'end_date',
			'condition'      => '<='
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

	/**
	 * @inheritDoc
	 */
	protected function _processFormValues(array $values){
		$filters = [];
		foreach ($values as $field => $value){
			if ($value !== NULL && $value !== ''){
				if ($field == 'start_date' || $field == 'end_date'){
					$value = strtotime(trim($value));
				}

				$filters[$field] = $value;
			}
		}

		return $filters;
	}
}