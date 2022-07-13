<?php

namespace Drupal\singpost_announcements\Form;

use Drupal;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Url;
use Drupal\singpost_announcements\Repositories\AnnouncementRepository;
use Drupal\singpost_base\Support\FormHelper;

/**
 * Class AnnouncementTableForm
 *
 * @package Drupal\singpost_announcements\Form
 */
class AnnouncementTableForm implements FormInterface{

	use MessengerTrait;

	/**
	 * @var \Drupal\singpost_announcements\Repositories\AnnouncementRepository
	 */
	protected $_announcement;

	/**
	 * @var \Drupal\Core\Datetime\DateFormatter
	 */
	protected $_dateformat;

	/**
	 * @var array
	 */
	protected $_filters;

	/**
	 * AnnouncementTableForm constructor.
	 *
	 * @param AnnouncementRepository $announcement
	 * @param DateFormatter $dateformat
	 * @param array $filters
	 */
	public function __construct(
		AnnouncementRepository $announcement,
		DateFormatter $dateformat,
		array $filters){
		$this->_announcement = $announcement;
		$this->_dateformat   = $dateformat;
		$this->_filters      = $filters;
	}

	/**
	 * @inheritDoc
	 */
	public function getFormId(){
		return 'announcement_table_form';
	}

	/**
	 * @inheritDoc
	 */
	public function buildForm(array $form, FormStateInterface $form_state){
		$limit = FormHelper::getPaginationLimitFromRequest(30);

		$header = [
			['data' => t('Title'), 'field' => 'title'],
			['data' => t('Start Date'), 'field' => 'start_date', 'sort' => 'desc'],
			['data' => t('End Date'), 'field' => 'end_date'],
			['data' => t('Published'), 'field' => 'published'],
			'actions' => t('Operations')
		];

		$pager = $this->_announcement->applyFilters($this->_filters)
		                             ->getTablePaginatedData($header, $limit);

		$announcements = $this->_announcement->getTableData($pager);

		$form['form'] = [
			'#type'       => 'container',
			'#attributes' => ['class' => ['form--inline clearfix']]
		];

		$form['form']['bulk_action'] = [
			'#type'    => 'select',
			'#title'   => t('Action'),
			'#options' => [
				'delete'    => t('Delete'),
				'publish'   => t('Publish'),
				'unpublish' => t('Unpublish')
			],
		];

		$form['form']['pagination'] = FormHelper::createPaginationLimit('singpost.announcements.admin',
			$limit);

		$form['buttons'] = [
			'#type'       => 'container',
			'#attributes' => ['class' => ['form-actions js-form-wrapper form-wrapper']]
		];

		$form['buttons']['apply'] = [
			'#type'       => 'submit',
			'#value'      => 'Apply Action',
			'#attributes' => ['class' => ['button']]
		];

		$form['table'] = [
			'#type'        => 'table',
			'#header'      => $header,
			'#tableselect' => TRUE,
			'#attributes'  => [
				'id' => $this->getFormId()
			]
		];

		if (!empty($announcements)){
			foreach ($announcements as $announcement){
				$form['table'][$announcement->id] = [
					'title'      => [
						'#plain_text' => $announcement->title
					],
					'start_date' => [
						'#plain_text' => $this->_dateformat->format($announcement->start_date)
					],
					'end_date'   => [
						'#plain_text' => $this->_dateformat->format($announcement->end_date)
					],
					'published'  => [
						'#theme'   => 'toggle_button',
						'#nid'     => $announcement->id,
						'#checked' => $announcement->published,
						'#action'  => Url::fromRoute('singpost.announcements.status')
					],
					'actions'    => [
						'#type'  => 'dropbutton',
						'#links' => [
							'edit'   => [
								'title' => t('Edit'),
								'url'   => Url::fromRoute('singpost.announcements.edit', [
									'id' => $announcement->id
								])
							],
							'delete' => [
								'title' => t('Delete'),
								'url'   => Url::fromRoute('singpost.announcements.delete', [
									'id' => $announcement->id
								]),
							],
						],
					]
				];
			}

			$form['count'] = $pager->renderSummary();
		}else{
			$form['table']['#empty'] = t('No announcement found.');
		}

		return $form;
	}

	/**
	 * @inheritDoc
	 */
	public function validateForm(array &$form, FormStateInterface $form_state){
		if (!$form_state->getValue('bulk_action')){
			$form_state->setErrorByName('bulk_action', t('Please select an action'));
		}

		if (!array_filter($form_state->getValue('table'))){
			$form_state->setErrorByName('table', t('Please select at least one item'));
		}
	}

	/**
	 * @inheritDoc
	 */
	public function submitForm(array &$form, FormStateInterface $form_state){
		$selected = array_filter($form_state->getValue('table'));

		Drupal::request()->getSession()->set('bulk_selected_announcements', $selected);
		$form_state->setRedirect('singpost.announcements.action',
			['action' => $form_state->getValue('bulk_action')]);
	}
}