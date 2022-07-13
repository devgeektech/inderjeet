<?php


namespace Drupal\singpost_toolbox_track_and_trace\Form\Status;


use Drupal;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Url;
use Drupal\singpost_base\Support\FormHelper;
use Drupal\singpost_toolbox_track_and_trace\Repositories\StatusRepository;
use Drupal\user\Entity\User;

/**
 * Class StatusTableForm
 *
 * @package Drupal\singpost_toolbox_track_and_trace\Form\Status
 */
class StatusTableForm implements FormInterface{

	use MessengerTrait;

	protected $_repository;

	/**
	 * StatusTableForm constructor.
	 *
	 * @param \Drupal\singpost_toolbox_track_and_trace\Repositories\StatusRepository $repository
	 */
	public function __construct(StatusRepository $repository){
		$this->_repository = $repository;
	}

	/**
	 * @return string
	 */
	public function getFormId(){
		return 'tnt_status_table_form';
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
			['data' => t('ID'), 'field' => 'id', 'sort' => 'asc'],
			['data' => t('Type'), 'field' => 'type'],
			['data' => t('Content'), 'field' => 'content'],
			['data' => t('Published'), 'field' => 'published'],
			['data' => t('Created at'), 'field' => 'created_at'],
			['data' => t('Created by'), 'field' => 'created_by'],
			'actions' => t('Operations'),
		];

		$pager = $this->_repository->getTablePaginatedData($header, $limit);

		$list_status = $this->_repository->getTableData($pager);

		$form['form'] = [
			'#type'       => 'container',
			'#attributes' => ['class' => ['form--inline clearfix']]
		];

		$form['form']['pagination'] = FormHelper::createPaginationLimit('singpost.toolbox.track_and_trace.status',
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
			'#type'       => 'table',
			'#header'     => $header,
			'#attributes' => [
				'id' => $this->getFormId()
			]
		];

		if (!empty($list_status)){
			foreach ($list_status as $item){
				$user = User::load($item->created_by);

				$form['table'][$item->id] = [
					'id'         => [
						'#plain_text' => $item->id
					],
					'name'       => [
						'#plain_text' => $item->type
					],
					'content'    => [
						'#plain_text' => $item->content
					],
					'published'  => [
						'#theme'   => 'toggle_button',
						'#nid'     => $item->id,
						'#checked' => $item->published,
						'#action'  => Url::fromRoute('singpost.toolbox.track_and_trace.status.change_status')
					],
					'created_at' => [
						'#plain_text' => Drupal::service('date.formatter')
						                       ->format($item->created_at)],
					'create_by'  => [
						'#plain_text' => $user->getAccountName()
					],
					'actions'    => [
						'#type'  => 'dropbutton',
						'#links' => [
							'edit'   => [
								'title' => t('Edit'),
								'url'   => Url::fromRoute('singpost.toolbox.track_and_trace.status.edit',
									[
										'id' => $item->id
									])
							],
							'delete' => [
								'title' => t('Delete'),
								'url'   => Url::fromRoute('singpost.toolbox.track_and_trace.status.delete',
									[
										'id' => $item->id
									]),
							],
						],
					]
				];
			}

			$form['count'] = $pager->renderSummary();
		}else{
			$form['table']['#empty'] = t('No status found.');
		}

		return $form;
	}

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 */
	public function submitForm(array &$form, FormStateInterface $form_state){ }

	/**
	 * @param array $form
	 * @param \Drupal\Core\Form\FormStateInterface $form_state
	 */
	public function validateForm(array &$form, FormStateInterface $form_state){ }
}