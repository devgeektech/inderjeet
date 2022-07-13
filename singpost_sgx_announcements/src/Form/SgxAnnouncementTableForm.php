<?php


namespace Drupal\singpost_sgx_announcements\Form;


use Drupal;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Url;
use Drupal\singpost_base\Support\FormHelper;
use Drupal\singpost_sgx_announcements\Repositories\SgxAnnouncementRepository;

/**
 * Class SgxAnnouncementTableForm
 *
 * @package Drupal\singpost_sgx_announcement\Form
 */
class SgxAnnouncementTableForm implements FormInterface{

	use MessengerTrait;

	/**
	 * @var \Drupal\singpost_sgx_announcements\Repositories\SgxAnnouncementRepository
	 */
	protected $_sgx_announcement;

	/**
	 * @var \Drupal\Core\Datetime\DateFormatter
	 */
	protected $_date_format;

	/**
	 * @var array
	 */
	protected $_filters;

	/**
	 * SgxAnnouncementTableForm constructor.
	 *
	 * @param \Drupal\singpost_sgx_announcements\Repositories\SgxAnnouncementRepository $sgx_announcement
	 * @param \Drupal\Core\Datetime\DateFormatter $date_format
	 * @param array $filters
	 */
	public function __construct(
		SgxAnnouncementRepository $sgx_announcement,
		DateFormatter $date_format,
		array $filters){
		$this->_sgx_announcement = $sgx_announcement;
		$this->_date_format      = $date_format;
		$this->_filters          = $filters;
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 *
	 * @return array
	 */
	public function buildForm(array $form, FormStateInterface $form_state){
		$limit = FormHelper::getPaginationLimitFromRequest(30);

		$header = [
			['data' => t('Title'), 'field' => 'title'],
			['data' => t('Date'), 'field' => 'date', 'sort' => 'desc'],
			['data' => t('Published'), 'field' => 'published'],
			'actions' => t('Operations')
		];

		$pager = $this->_sgx_announcement->applyFilters($this->_filters)
		                                 ->getTablePaginatedData($header, $limit);

		$sgx_announcements = $this->_sgx_announcement->getTableData($pager);

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

		$form['form']['pagination'] = FormHelper::createPaginationLimit('singpost.sgx.announcements.admin',
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

		if (!empty($sgx_announcements)){
			foreach ($sgx_announcements as $item){
				$form['table'][$item->id] = [
					'title'     => [
						'#plain_text' => $item->title
					],
					'date'      => [
						'#plain_text' => $this->_date_format->format($item->date)
					],
					'published' => [
						'#theme'   => 'toggle_button',
						'#nid'     => $item->id,
						'#checked' => $item->published,
						'#action'  => Url::fromRoute('singpost.sgx.announcements.status')
					],
					'actions'   => [
						'#type'  => 'dropbutton',
						'#links' => [
							'edit'   => [
								'title' => t('Edit'),
								'url'   => Url::fromRoute('singpost.sgx.announcements.edit', [
									'id' => $item->id
								])
							],
							'delete' => [
								'title' => t('Delete'),
								'url'   => Url::fromRoute('singpost.sgx.announcements.delete', [
									'id' => $item->id
								])
							]
						],
					]
				];
			}

			$form['count'] = $pager->renderSummary();
		}else{
			$form['table']['#empty'] = t('No SGX announcement found.');
		}

		return $form;
	}

	/**
	 * @return string
	 */
	public function getFormId(){
		return 'sgx_announcement_table_form';
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 */
	public function submitForm(array &$form, FormStateInterface $form_state){
		$selected = array_filter($form_state->getValue('table'));

		Drupal::request()->getSession()->set('bulk_selected_sgx_announcements', $selected);
		$form_state->setRedirect('singpost.sgx.announcements.action',
			['action' => $form_state->getValue('bulk_action')]);
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 */
	public function validateForm(array &$form, FormStateInterface $form_state){
		if (!$form_state->getValue('bulk_action')){
			$form_state->setErrorByName('bulk_action', t('Please select an action'));
		}

		if (!array_filter($form_state->getValue('table'))){
			$form_state->setErrorByName('table', t('Please select at least one item'));
		}
	}
}