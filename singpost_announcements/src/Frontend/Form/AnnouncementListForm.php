<?php


namespace Drupal\singpost_announcements\Frontend\Form;


use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\singpost_announcements\Repositories\AnnouncementRepository;

/**
 * Class AnnouncementListForm
 *
 * @package Drupal\singpost_announcements\Frontend\Form
 */
class AnnouncementListForm implements FormInterface{

	/**
	 * @var \Drupal\singpost_announcements\Repositories\AnnouncementRepository
	 */
	protected $_announcement;

	/**
	 * @var array
	 */
	protected $_filters;

	/**
	 * AnnouncementListForm constructor.
	 *
	 * @param \Drupal\singpost_announcements\Repositories\AnnouncementRepository $repository
	 * @param array $filters
	 */
	public function __construct(AnnouncementRepository $repository, array $filters){
		$this->_announcement = $repository;
		$this->_filters      = $filters;
	}

	/**
	 * @return string
	 */
	public function getFormId(){
		return 'announcement_list_form';
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 *
	 * @return array|void
	 */
	public function buildForm(array $form, FormStateInterface $form_state){
		$limit = 5;

		$header = [
			['data' => t('Start Date'), 'field' => 'start_date', 'sort' => 'desc'],
		];

		$pager = $this->_announcement->applyFilters($this->_filters)
		                             ->getTablePaginatedData($header, $limit);

		$announcements = $this->_announcement->getTableData($pager);

		$form['table'] = [
			'#theme'         => 'singpost_announcements',
			'#announcements' => $announcements,
			'#cache'         => [
				'max-cache' => 0
			]
		];

		$form['pager'] = [
			'#type' => 'pager',
		];

		return $form;
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 */
	public function validateForm(array &$form, FormStateInterface $form_state){ }

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 */
	public function submitForm(array &$form, FormStateInterface $form_state){ }
}